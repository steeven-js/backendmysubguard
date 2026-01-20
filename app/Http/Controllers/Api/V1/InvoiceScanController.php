<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Services\ApiLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

/**
 * Invoice Scanner Controller
 * Uses OpenAI Vision API to extract subscription data from invoice images
 */
class InvoiceScanController extends Controller
{
    /**
     * Scan an invoice image and extract subscription data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        $startTime = microtime(true);

        $validated = $request->validate([
            'image_base64' => 'required|string|max:10485760', // ~8MB limit for base64
        ]);

        try {
            // Log outgoing OpenAI request
            $openaiStartTime = microtime(true);

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $this->getPrompt()],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,' . $validated['image_base64'],
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 500,
            ]);

            $openaiDuration = (int) ((microtime(true) - $openaiStartTime) * 1000);
            $content = $response->choices[0]->message->content;

            // Log OpenAI call
            ApiLogService::logOutgoing(
                service: ApiLog::SERVICE_OPENAI,
                method: 'POST',
                endpoint: 'chat/completions (gpt-4o vision)',
                requestBody: [
                    'model' => 'gpt-4o',
                    'prompt' => 'Invoice analysis prompt',
                    'image_size' => strlen($validated['image_base64']) . ' chars',
                ],
                responseBody: [
                    'content' => $content,
                    'usage' => $response->usage?->toArray() ?? null,
                ],
                statusCode: 200,
                durationMs: $openaiDuration
            );

            // Clean markdown code blocks from response
            $content = $this->cleanJsonResponse($content);

            // Parse JSON response from OpenAI
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('InvoiceScan: OpenAI returned invalid JSON', [
                    'content' => $content,
                ]);

                $totalDuration = (int) ((microtime(true) - $startTime) * 1000);

                // Log incoming request with partial success
                ApiLogService::logIncoming(
                    request: $request,
                    service: ApiLog::SERVICE_APP,
                    statusCode: 200,
                    responseBody: ['message' => 'Partial extraction - invalid JSON from OpenAI'],
                    durationMs: $totalDuration
                );

                return response()->json([
                    'data' => [
                        'service_name' => null,
                        'amount' => null,
                        'currency' => null,
                        'billing_date' => null,
                        'frequency' => null,
                    ],
                    'message' => 'Unable to parse invoice data',
                ]);
            }

            $totalDuration = (int) ((microtime(true) - $startTime) * 1000);

            // Log successful incoming request
            ApiLogService::logIncoming(
                request: $request,
                service: ApiLog::SERVICE_APP,
                statusCode: 200,
                responseBody: ['data' => $data],
                durationMs: $totalDuration
            );

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            Log::error('InvoiceScan: OpenAI API error', [
                'error' => $e->getMessage(),
            ]);

            $totalDuration = (int) ((microtime(true) - $startTime) * 1000);

            // Log failed OpenAI call
            ApiLogService::logOutgoing(
                service: ApiLog::SERVICE_OPENAI,
                method: 'POST',
                endpoint: 'chat/completions (gpt-4o vision)',
                requestBody: ['model' => 'gpt-4o'],
                statusCode: 500,
                durationMs: $totalDuration,
                errorMessage: $e->getMessage()
            );

            // Log failed incoming request
            ApiLogService::logIncoming(
                request: $request,
                service: ApiLog::SERVICE_APP,
                statusCode: 500,
                durationMs: $totalDuration,
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'error' => 'Failed to analyze invoice',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clean markdown code blocks from OpenAI response
     */
    private function cleanJsonResponse(string $content): string
    {
        // Remove ```json and ``` markers
        $content = preg_replace('/^```json\s*/i', '', trim($content));
        $content = preg_replace('/```\s*$/', '', $content);

        return trim($content);
    }

    /**
     * Get the prompt for OpenAI Vision
     */
    private function getPrompt(): string
    {
        return <<<'PROMPT'
Analyze this invoice/receipt image and extract subscription information.
Return ONLY a valid JSON object with these exact fields:
{
  "service_name": "string or null",
  "amount": number or null,
  "currency": "string or null (e.g. EUR, USD)",
  "billing_date": "string ISO8601 or null",
  "frequency": "monthly|yearly|weekly|quarterly or null"
}

Rules:
- Extract the subscription/service name (e.g., Netflix, Spotify, Adobe)
- Extract the price amount as a number (e.g., 15.99)
- Detect the currency from symbols (€=EUR, $=USD, £=GBP)
- If a date is visible, format it as ISO8601 (YYYY-MM-DD)
- Detect frequency from words like "monthly", "per month", "annual", "yearly"
- If a field cannot be determined with confidence, set it to null
- Respond with valid JSON only, no markdown, no explanation
PROMPT;
    }
}

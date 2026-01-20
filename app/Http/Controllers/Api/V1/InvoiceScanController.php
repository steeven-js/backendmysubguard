<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
        $validated = $request->validate([
            'image_base64' => 'required|string|max:10485760', // ~8MB limit for base64
        ]);

        try {
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

            $content = $response->choices[0]->message->content;

            // Parse JSON response from OpenAI
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('InvoiceScan: OpenAI returned invalid JSON', [
                    'content' => $content,
                ]);

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

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            Log::error('InvoiceScan: OpenAI API error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to analyze invoice',
                'message' => $e->getMessage(),
            ], 500);
        }
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

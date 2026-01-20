<?php

namespace App\Services;

use App\Models\ApiLog;
use Illuminate\Http\Request;

class ApiLogService
{
    /**
     * Log an incoming API request
     */
    public static function logIncoming(
        Request $request,
        string $service,
        ?int $statusCode = null,
        ?array $responseBody = null,
        ?int $durationMs = null,
        ?string $errorMessage = null
    ): ApiLog {
        return ApiLog::create([
            'type' => ApiLog::TYPE_INCOMING,
            'service' => $service,
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'status' => $errorMessage ? ApiLog::STATUS_ERROR : ApiLog::STATUS_SUCCESS,
            'status_code' => $statusCode,
            'request_headers' => self::sanitizeHeaders($request->headers->all()),
            'request_body' => self::sanitizeBody($request->all()),
            'response_body' => $responseBody,
            'duration_ms' => $durationMs,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Log an outgoing API request (e.g., to OpenAI)
     */
    public static function logOutgoing(
        string $service,
        string $method,
        string $endpoint,
        ?array $requestBody = null,
        ?array $responseBody = null,
        ?int $statusCode = null,
        ?int $durationMs = null,
        ?string $errorMessage = null
    ): ApiLog {
        return ApiLog::create([
            'type' => ApiLog::TYPE_OUTGOING,
            'service' => $service,
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $errorMessage ? ApiLog::STATUS_ERROR : ApiLog::STATUS_SUCCESS,
            'status_code' => $statusCode,
            'request_body' => self::sanitizeBody($requestBody),
            'response_body' => $responseBody,
            'duration_ms' => $durationMs,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Sanitize headers to remove sensitive data
     */
    private static function sanitizeHeaders(array $headers): array
    {
        $sensitiveKeys = ['authorization', 'cookie', 'x-api-key'];

        foreach ($sensitiveKeys as $key) {
            if (isset($headers[$key])) {
                $headers[$key] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize body to remove/truncate sensitive or large data
     */
    private static function sanitizeBody(?array $body): ?array
    {
        if (!$body) {
            return null;
        }

        // Truncate base64 images
        if (isset($body['image_base64'])) {
            $body['image_base64'] = '[BASE64_IMAGE_' . strlen($body['image_base64']) . '_CHARS]';
        }

        // Truncate large content fields
        foreach ($body as $key => $value) {
            if (is_string($value) && strlen($value) > 1000) {
                $body[$key] = substr($value, 0, 500) . '... [TRUNCATED]';
            }
        }

        return $body;
    }
}

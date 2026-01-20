<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\CheckUserMilestones;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Story 8.6: Analytics API Endpoint
 * Receives analytics events from iOS app
 */
class AnalyticsController extends Controller
{
    /**
     * Receive analytics events
     * POST /api/v1/analytics
     *
     * Supports both single events and batch uploads
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Single event or array of events
            'events' => 'sometimes|array',
            'events.*.anonymous_user_id' => 'required|string|max:64',
            'events.*.event_type' => 'required|string|max:50',
            'events.*.properties' => 'sometimes|array',
            'events.*.app_version' => 'sometimes|string|max:20',
            'events.*.os_version' => 'sometimes|string|max:20',
            'events.*.device_model' => 'sometimes|string|max:50',
            'events.*.event_timestamp' => 'sometimes|date',

            // Single event format (backwards compatible)
            'anonymous_user_id' => 'required_without:events|string|max:64',
            'event_type' => 'required_without:events|string|max:50',
            'properties' => 'sometimes|array',
            'app_version' => 'sometimes|string|max:20',
            'os_version' => 'sometimes|string|max:20',
            'device_model' => 'sometimes|string|max:50',
            'event_timestamp' => 'sometimes|date',
        ]);

        $eventsToStore = [];

        // Handle batch events
        if (isset($validated['events'])) {
            $eventsToStore = $validated['events'];
        } else {
            // Handle single event
            $eventsToStore = [[
                'anonymous_user_id' => $validated['anonymous_user_id'],
                'event_type' => $validated['event_type'],
                'properties' => $validated['properties'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'os_version' => $validated['os_version'] ?? null,
                'device_model' => $validated['device_model'] ?? null,
                'event_timestamp' => $validated['event_timestamp'] ?? now(),
            ]];
        }

        $stored = 0;
        foreach ($eventsToStore as $eventData) {
            try {
                AnalyticsEvent::create([
                    'anonymous_user_id' => $eventData['anonymous_user_id'],
                    'event_type' => $eventData['event_type'],
                    'properties' => $eventData['properties'] ?? null,
                    'app_version' => $eventData['app_version'] ?? null,
                    'os_version' => $eventData['os_version'] ?? null,
                    'device_model' => $eventData['device_model'] ?? null,
                    'event_timestamp' => $eventData['event_timestamp'] ?? now(),
                ]);
                $stored++;
            } catch (\Exception $e) {
                Log::warning('Failed to store analytics event', [
                    'event_type' => $eventData['event_type'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Check milestones after receiving events (async)
        if ($stored > 0) {
            CheckUserMilestones::dispatch()->delay(now()->addMinutes(1));
        }

        return response()->json([
            'message' => 'Events received',
            'stored' => $stored,
            'total' => count($eventsToStore),
        ], 201);
    }

    /**
     * Health check endpoint
     * GET /api/v1/analytics/health
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}

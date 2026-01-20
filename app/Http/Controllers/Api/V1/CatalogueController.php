<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CatalogueItem;
use App\Models\Suggestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CatalogueController extends Controller
{
    /**
     * Get all catalogue items for sync
     * GET /api/v1/catalogue
     */
    public function index(): JsonResponse
    {
        $items = CatalogueItem::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'typical_amount' => (float) $item->typical_amount,
                'logo_url' => $item->logo_url ? asset('storage/' . $item->logo_url) : null,
                'category' => $item->category,
                'updated_at' => $item->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => $items->count(),
                'synced_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Search catalogue items
     * GET /api/v1/catalogue/search?q=netflix
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $items = CatalogueItem::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'typical_amount' => (float) $item->typical_amount,
                    'logo_url' => $item->logo_url ? asset('storage/' . $item->logo_url) : null,
                    'category' => $item->category,
                ];
            });

        return response()->json(['data' => $items]);
    }

    /**
     * Submit a community suggestion
     * POST /api/v1/catalogue/suggestions
     */
    public function suggest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create or increment suggestion count
        $suggestion = Suggestion::incrementOrCreate($validated['name']);

        \Log::info('Catalogue suggestion received', [
            'name' => $validated['name'],
            'submission_count' => $suggestion->submission_count,
        ]);

        return response()->json([
            'message' => 'Suggestion received',
            'name' => $validated['name'],
        ], 201);
    }
}

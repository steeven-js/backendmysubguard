<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CatalogueController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\InvoiceScanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1 - Public endpoints (no auth required for catalogue)
Route::prefix('v1')->group(function () {
    // Catalogue endpoints
    Route::get('/catalogue', [CatalogueController::class, 'index']);
    Route::get('/catalogue/search', [CatalogueController::class, 'search']);
    Route::post('/catalogue/suggestions', [CatalogueController::class, 'suggest']);

    // Analytics endpoints (Story 8.6)
    Route::post('/analytics', [AnalyticsController::class, 'store']);
    Route::get('/analytics/health', [AnalyticsController::class, 'health']);

    // Invoice Scanner endpoint (OpenAI Vision)
    Route::post('/invoice/scan', [InvoiceScanController::class, 'scan']);
});

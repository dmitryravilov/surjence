<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HeadlineService;
use Illuminate\Http\JsonResponse;

class HeadlineController extends Controller
{
    public function __construct(
        private readonly HeadlineService $headlineService
    ) {}

    public function index(): JsonResponse
    {
        $headlines = $this->headlineService->fetchAndProcessHeadlines();

        return response()->json([
            'data' => $headlines,
            'count' => count($headlines),
            'meta' => [
                'fetched_at' => now()->toIso8601String(),
            ],
        ]);
    }
}

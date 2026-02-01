<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    public function index(): JsonResponse
    {
        $themes = Theme::withCount('headlines')
            ->orderBy('name')
            ->get()
            ->map(fn ($theme) => [
                'id' => $theme->id,
                'name' => $theme->name,
                'description' => $theme->description,
                'color' => $theme->color,
                'headlines_count' => $theme->headlines_count,
            ]);

        return response()->json([
            'data' => $themes,
            'count' => $themes->count(),
        ]);
    }
}

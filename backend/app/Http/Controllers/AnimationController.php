<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnimationController extends Controller
{
    public function getConfig(Request $request)
    {
        Log::info("AnimationController: Getting animation configuration");

        // For now, return default configuration
        // In the future, this could be dynamic based on URL, user settings, or database
        $config = [
            'type' => 'down&up',
            'speed' => 3,
            'duration' => null, // null for infinite
            'pauseTime' => 2000, // milliseconds to pause at top/bottom
        ];

        Log::info("AnimationController: Returning config", $config);

        return response()->json($config);
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:none,down,down&reset,down&up,step',
            'speed' => 'integer|min:1|max:20',
            'duration' => 'nullable|integer|min:1000',
            'pauseTime' => 'integer|min:500|max:10000',
        ]);

        Log::info("AnimationController: Updating animation configuration", $validated);

        // For now, just return the updated config
        // In the future, this would save to database
        return response()->json([
            'message' => 'Animation configuration updated',
            'config' => $validated
        ]);
    }
}
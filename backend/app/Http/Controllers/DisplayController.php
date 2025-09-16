<?php

namespace App\Http\Controllers;

use App\Models\Display;
use App\Enums\DisplayType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class DisplayController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'display_type' => [
                'required',
                'string',
                Rule::in(['New', 'Inactive', 'Public Facing', 'Internal Use', 'Advertising'])
            ],
            'location' => 'nullable|string|max:255',
        ]);

        // Find existing display with same device_id or create new one
        $display = Display::firstOrCreate(
            ['config->device_id' => $validated['device_id']],
            [
                'name' => $validated['name'],
                'display_type' => $validated['display_type'],
                'location' => $validated['location'] ?? 'Unknown',
                'status' => 'connected',
                'last_seen' => now(),
                'config' => ['device_id' => $validated['device_id']]
            ]
        );

        // Update existing display if found
        if (!$display->wasRecentlyCreated) {
            $display->update([
                'name' => $validated['name'],
                'display_type' => $validated['display_type'],
                'location' => $validated['location'] ?? $display->location,
                'status' => 'connected',
                'last_seen' => now(),
                'config' => array_merge($display->config ?? [], ['device_id' => $validated['device_id']])
            ]);
        }

        return response()->json([
            'success' => true,
            'display' => [
                'id' => $display->id,
                'name' => $display->name,
                'display_type' => $display->display_type,
                'location' => $display->location,
                'status' => $display->status,
                'last_seen' => $display->last_seen,
                'auth_token' => $display->auth_token
            ]
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'display_id' => 'required|exists:displays,id'
        ]);

        $display = Display::find($validated['display_id']);
        $display->update([
            'status' => 'connected',
            'last_seen' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function checkDisplay(Request $request, string $deviceId): JsonResponse
    {
        $display = Display::where('config->device_id', $deviceId)->first();

        if ($display) {
            return response()->json([
                'success' => true,
                'display' => [
                    'id' => $display->id,
                    'name' => $display->name,
                    'display_type' => $display->display_type,
                    'location' => $display->location,
                    'status' => $display->status,
                    'last_seen' => $display->last_seen,
                    'auth_token' => $display->auth_token
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'display' => null
        ]);
    }

    public function findByAccessToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token' => 'required|string|size:6'
        ]);

        $display = Display::with('program')->where('access_token', strtoupper($validated['access_token']))->first();

        if ($display) {
            $display->update([
                'status' => 'connected',
                'last_seen' => now(),
                'initialized' => true
            ]);

            return response()->json([
                'success' => true,
                'display' => [
                    'id' => $display->id,
                    'name' => $display->name,
                    'program' => $display->program ? $display->program->name : null,
                    'location' => $display->location,
                    'status' => $display->status,
                    'last_seen' => $display->last_seen,
                    'auth_token' => $display->auth_token,
                    'access_token' => $display->access_token,
                    'initialized' => $display->initialized,
                    'created_at' => $display->created_at
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid access token',
            'display' => null
        ]);
    }

    public function findByAuthToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auth_token' => 'required|string|size:32'
        ]);

        $display = Display::with('program')->where('auth_token', $validated['auth_token'])->first();

        if ($display) {
            $display->update([
                'status' => 'connected',
                'last_seen' => now()
            ]);

            return response()->json([
                'success' => true,
                'display' => [
                    'id' => $display->id,
                    'name' => $display->name,
                    'program' => $display->program ? $display->program->name : null,
                    'location' => $display->location,
                    'status' => $display->status,
                    'last_seen' => $display->last_seen,
                    'auth_token' => $display->auth_token,
                    'access_token' => $display->access_token,
                    'initialized' => $display->initialized,
                    'created_at' => $display->created_at
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid auth token',
            'display' => null
        ]);
    }
}
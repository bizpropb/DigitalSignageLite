<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Display;
use App\Events\DisplayTestMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DisplayTestController extends Controller
{
    public function __construct()
    {
        // Ensure only authenticated admin users can access this controller
        $this->middleware('auth:sanctum');
    }

    /**
     * Send a test message to a specific display
     */
    public function sendTestMessage(Request $request, Display $display): JsonResponse
    {
        $request->validate([
            'message' => 'string|max:255|nullable'
        ]);

        $message = $request->input('message', 'Test message from admin');

        // Broadcast test message to the specific display
        broadcast(new DisplayTestMessage($display->id, $message, $display->name));

        //  FIX: Add logging after broadcast
        \Log::info('Test message broadcasted to display ID:', ['id' => $display->id]);

        return response()->json([
            'success' => true,
            'message' => "Test message sent to display '{$display->name}'"
        ]);
    }
}

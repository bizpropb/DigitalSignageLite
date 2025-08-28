<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class TestController extends Controller
{
    public function sendTestMessage(Request $request)
    {
        $message = $request->input('message', 'Hello from Laravel!');
        
        // Broadcast test message to display clients
        broadcast(new \App\Events\TestMessage($message));
        
        return response()->json([
            'success' => true,
            'message' => 'Test message sent: ' . $message
        ]);
    }
}
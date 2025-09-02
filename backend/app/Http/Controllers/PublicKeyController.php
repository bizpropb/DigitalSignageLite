<?php

namespace App\Http\Controllers;

use App\Services\MessageSigningService;
use Illuminate\Http\JsonResponse;

class PublicKeyController extends Controller
{
    public function __construct(
        private MessageSigningService $signingService
    ) {}

    /**
     * Get public key for client-side JWT verification
     */
    public function getPublicKey(): JsonResponse
    {
        try {
            $publicKey = $this->signingService->getPublicKey();
            
            return response()->json([
                'success' => true,
                'public_key' => $publicKey,
                'algorithm' => 'RS256'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to retrieve public key'
            ], 500);
        }
    }
}
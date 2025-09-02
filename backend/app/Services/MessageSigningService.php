<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class MessageSigningService
{
    private string $privateKeyPath;
    private string $publicKeyPath;

    public function __construct()
    {
        $this->privateKeyPath = storage_path('keys/private.pem');
        $this->publicKeyPath = storage_path('keys/public.pem');
    }

    /**
     * Sign a message with RSA private key using JWT
     */
    public function signMessage(array $data): string
    {
        $privateKey = file_get_contents($this->privateKeyPath);
        
        $payload = [
            'data' => $data,
            'iat' => time(),
            'exp' => time() + 300, // 5 minutes expiry
            'iss' => config('app.name', 'Presenter-V3')
        ];

        return JWT::encode($payload, $privateKey, 'RS256');
    }

    /**
     * Verify a JWT message signature
     */
    public function verifyMessage(string $jwt): array
    {
        $publicKey = file_get_contents($this->publicKeyPath);
        
        try {
            $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid message signature: ' . $e->getMessage());
        }
    }

    /**
     * Get public key content for client distribution
     */
    public function getPublicKey(): string
    {
        return file_get_contents($this->publicKeyPath);
    }
}
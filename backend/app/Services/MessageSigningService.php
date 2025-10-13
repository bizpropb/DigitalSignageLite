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
        \Log::info("MessageSigningService signMessage() START");
        \Log::info("Private key path:", ['path' => $this->privateKeyPath]);
        \Log::info("Private key file exists?", ['exists' => file_exists($this->privateKeyPath)]);

        if (!file_exists($this->privateKeyPath)) {
            \Log::error("Private key file does not exist!");
            throw new \Exception("Private key file not found: " . $this->privateKeyPath);
        }

        $privateKey = file_get_contents($this->privateKeyPath);
        \Log::info("Private key loaded, length:", ['length' => strlen($privateKey)]);

        $payload = [
            'data' => $data,
            'iat' => time(),
            'exp' => time() + 300, // 5 minutes expiry
            'iss' => config('app.name', 'DigitalSignageLite')
        ];

        \Log::info("JWT payload prepared:", $payload);

        try {
            $jwt = JWT::encode($payload, $privateKey, 'RS256');
            \Log::info("JWT encoded successfully, length:", ['length' => strlen($jwt)]);
            \Log::info("MessageSigningService signMessage() END SUCCESS");
            return $jwt;
        } catch (\Exception $e) {
            \Log::error("JWT encoding failed:", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
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

<?php

namespace EISALL\TaskHubConnector\Support;

/**
 * Mirror of TaskHub's App\Services\Connector\RequestSigner. The two must
 * stay in step: HMAC-SHA256 over "METHOD|/path|timestamp|nonce|sha256(body)".
 */
class SignatureVerifier
{
    /**
     * Seconds of clock skew tolerated between TaskHub and this app.
     */
    public const CLOCK_WINDOW = 300;

    public const HEADER_APP = 'X-TaskHub-App';
    public const HEADER_TIMESTAMP = 'X-TaskHub-Timestamp';
    public const HEADER_NONCE = 'X-TaskHub-Nonce';
    public const HEADER_SIGNATURE = 'X-TaskHub-Signature';

    public function verify(
        string $secret,
        string $method,
        string $path,
        string $timestamp,
        string $nonce,
        string $body,
        string $signature
    ): bool {
        if (abs(time() - (int) $timestamp) > self::CLOCK_WINDOW) {
            return false;
        }

        $payload = implode('|', [
            strtoupper($method),
            '/'.ltrim($path, '/'),
            $timestamp,
            $nonce,
            hash('sha256', $body),
        ]);

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }
}

<?php

namespace EISALL\TaskHubConnector\Http\Middleware;

use Closure;
use EISALL\TaskHubConnector\Support\SignatureVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Rejects any request not signed with this app's shared secret. Nonces are
 * remembered for the clock window so a captured request cannot be replayed
 * even inside it.
 */
class VerifyTaskHubSignature
{
    public function __construct(protected SignatureVerifier $verifier)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $secret = config('taskhub.secret');
        $slug = config('taskhub.slug');

        $timestamp = (string) $request->header(SignatureVerifier::HEADER_TIMESTAMP, '');
        $nonce = (string) $request->header(SignatureVerifier::HEADER_NONCE, '');
        $signature = (string) $request->header(SignatureVerifier::HEADER_SIGNATURE, '');

        $valid = $secret
            && $slug
            && $request->header(SignatureVerifier::HEADER_APP) === $slug
            && $timestamp !== '' && $nonce !== '' && $signature !== ''
            && $this->verifier->verify(
                $secret,
                $request->method(),
                $request->getPathInfo(),
                $timestamp,
                $nonce,
                $request->getContent(),
                $signature
            );

        if (! $valid) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Cache::add is atomic: false means the nonce was already used.
        if (! Cache::add('taskhub-nonce:'.$nonce, 1, SignatureVerifier::CLOCK_WINDOW * 2)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}

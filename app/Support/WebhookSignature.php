<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Phase 2 §12 — validate webhook signatures when the provider sends one.
 */
class WebhookSignature
{
    /**
     * @return true|string true on ok, error message on failure
     */
    public static function validate(Request $request, string $source): true|string
    {
        $config = config("webhooks.sources.{$source}", []);
        $secret = trim((string) ($config['secret'] ?? ''));
        $bearer = trim((string) ($config['bearer'] ?? ''));

        // Optional bearer / shared token (query or Authorization)
        if ($bearer !== '') {
            $auth = (string) $request->bearerToken();
            $queryToken = (string) $request->query('token', $request->header('X-Webhook-Token', ''));
            if ($auth !== '' || $queryToken !== '') {
                $provided = $auth !== '' ? $auth : $queryToken;
                if (! hash_equals($bearer, $provided)) {
                    return 'Invalid webhook bearer/token';
                }

                return true;
            }
        }

        $signature = self::extractSignature($request, $config['headers'] ?? []);

        // Docs: validate signature *if provided*
        if ($signature === null || $signature === '') {
            return true;
        }

        if ($secret === '') {
            Log::warning("Webhook {$source}: signature header present but no secret configured — accepting");

            return true;
        }

        $raw = $request->getContent() ?: json_encode($request->all(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $candidates = [
            hash_hmac('sha256', $raw, $secret),
            hash_hmac('sha256', $raw, $secret, true),
            base64_encode(hash_hmac('sha256', $raw, $secret, true)),
            hash_hmac('sha1', $raw, $secret),
        ];

        $normalized = strtolower(preg_replace('#^(sha256=|sha1=)#i', '', trim($signature)) ?? '');

        foreach ($candidates as $candidate) {
            $check = is_string($candidate) && ! self::looksBinary($candidate)
                ? strtolower($candidate)
                : strtolower(bin2hex((string) $candidate));

            if (is_string($candidate) && hash_equals(strtolower($candidate), $normalized)) {
                return true;
            }
            if (hash_equals($check, $normalized)) {
                return true;
            }
            if (hash_equals(base64_encode(hash_hmac('sha256', $raw, $secret, true)), $signature)) {
                return true;
            }
        }

        return 'Invalid webhook signature';
    }

    protected static function extractSignature(Request $request, array $headers): ?string
    {
        foreach ($headers as $header) {
            $value = $request->header($header);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected static function looksBinary(string $value): bool
    {
        return ! ctype_print($value) || preg_match('/[^\x20-\x7E]/', $value) === 1;
    }
}

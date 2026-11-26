<?php

namespace App\Libraries;

/**
 * Helper JWT ringan (HMAC-SHA256) tanpa dependensi eksternal.
 */
class JwtHelper
{
    private static function secret(): string
    {
        $key = trim((string) env('app.jwtSecret', ''));
        if ($key === '') {
            $key = trim((string) env('encryption.key', ''));
        }
        if ($key === '') {
            $key = hash('sha256', 'singgah-medan-jwt|' . (string) env('database.default.password', 'default-key'));
        }

        return $key;
    }

    private static function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $data): string|false
    {
        $pad = strlen($data) % 4;
        if ($pad > 0) {
            $data .= str_repeat('=', 4 - $pad);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }

    public static function encode(array $payload, int $ttlSeconds = 86400): string
    {
        $now               = time();
        $payload['iat']    = $now;
        $payload['exp']    = $now + $ttlSeconds;
        $headerEncoded     = self::b64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payloadEncoded    = self::b64UrlEncode(json_encode($payload));
        $signature         = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, self::secret(), true);
        $signatureEncoded  = self::b64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public static function decode(string $token): ?array
    {
        $parts = explode('.', trim($token));
        if (count($parts) !== 3) {
            return null;
        }
        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $expectedSig = self::b64UrlEncode(hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, self::secret(), true));
        if (! hash_equals($expectedSig, $signatureEncoded)) {
            return null;
        }

        $payloadRaw = self::b64UrlDecode($payloadEncoded);
        if ($payloadRaw === false) {
            return null;
        }
        $payload = json_decode($payloadRaw, true);
        if (! is_array($payload)) {
            return null;
        }
        if (isset($payload['exp']) && (int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }
}


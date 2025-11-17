<?php

namespace Src\Helpers;

class Jwt
{
    private const ALGORITHM = 'HS256';
    private const HASH_ALGO = 'sha256';

    public static function sign(array $payload, string $secret): string
    {
        $header = ['typ' => 'JWT', 'alg' => self::ALGORITHM];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signatureData = hash_hmac(self::HASH_ALGO, "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signatureData);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    public static function verify(string $token, string $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $expectedSignature = self::base64UrlEncode(
            hash_hmac(self::HASH_ALGO, "$headerEncoded.$payloadEncoded", $secret, true)
        );

        if (!hash_equals($signatureEncoded, $expectedSignature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    public static function decode(string $token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $header = json_decode(self::base64UrlDecode($headerEncoded), true);
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        return [
            'header' => $header,
            'payload' => $payload,
            'signature' => $signatureEncoded
        ];
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
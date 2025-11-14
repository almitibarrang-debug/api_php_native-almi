<?php
namespace Src\Helpers;

class Jwt
{
    public static function sign($payload, $secret)
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        
        $header64 = self::base64UrlEncode(json_encode($header));
        $payload64 = self::base64UrlEncode(json_encode($payload));
        $signature = self::base64UrlEncode(hash_hmac('sha256', "$header64.$payload64", $secret, true));
        
        return "$header64.$payload64.$signature";
    }
    
    public static function verify($token, $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        [$header64, $payload64, $signature] = $parts;
        
        $expectedSignature = self::base64UrlEncode(hash_hmac('sha256', "$header64.$payload64", $secret, true));
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        $payload = json_decode(self::base64UrlDecode($payload64), true);
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    public static function decode($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }
        
        [$header64, $payload64, $signature] = $parts;
        
        $header = json_decode(self::base64UrlDecode($header64), true);
        $payload = json_decode(self::base64UrlDecode($payload64), true);
        
        return [
            'header' => $header,
            'payload' => $payload,
            'signature' => $signature
        ];
    }
    
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
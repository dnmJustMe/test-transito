<?php
require_once __DIR__ . '/../config/config.php';

class JWT {
    private static $secret = JWT_SECRET;
    
    public static function generate($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = self::base64url_encode($header);
        $base64Payload = self::base64url_encode($payload);
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $base64Signature = self::base64url_encode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    public static function verify($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];
        
        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, self::$secret, true);
        $expectedSignature = self::base64url_encode($expectedSignature);
        
        if (!hash_equals($signature, $expectedSignature)) {
            return false;
        }
        
        $decodedPayload = json_decode(self::base64url_decode($payload), true);
        
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return false;
        }
        
        return $decodedPayload;
    }
    
    private static function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64url_decode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
    
    public static function getTokenFromHeader() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (strpos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        return null;
    }
    
    public static function getCurrentUser() {
        $token = self::getTokenFromHeader();
        if (!$token) {
            return null;
        }
        
        $payload = self::verify($token);
        return $payload;
    }
}
?>
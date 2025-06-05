<?php
class AuthMiddleware {
    public static function verify() {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token manquant']);
            exit;
        }
        
        try {
            return JWT::verify($token);
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            exit;
        }
    }
}
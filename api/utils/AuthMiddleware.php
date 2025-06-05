<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Employee.php';

// AuthMiddleware is a class that handles authentication for API requests
class AuthMiddleware {
    public static function authenticate() {
        // Check if the request method is OPTIONS (CORS preflight request)
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? null;

        // If the token is not provided, return an error response
        if (!$token) {
            Response::error('Token d\'authentification manquant', 401);
        }

        $db = (new Database())->getConnection();
        $employee = new Employee($db);
        $employee->token = str_replace('Bearer ', '', $token);

        if (!$employee->validateToken()) {
            Response::error('Token invalide ou expiré', 401);
        }

        return $employee->id;
    }
}
?>
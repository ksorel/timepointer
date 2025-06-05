<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/jwt.php';

$action = $_GET['action'] ?? '';

try {
    $pdo = Database::getInstance()->getConnection();
    
    switch ($action) {
        case 'login':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            if (empty($data['email']) || empty($data['password'])) {
                throw new Exception('Email et mot de passe requis');
            }
            
            // Récupération utilisateur
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
            $stmt->execute([$data['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($data['password'], $user['password'])) {
                throw new Exception('Identifiants incorrects');
            }
            
            // Génération JWT
            $token = JWT::generate([
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
            
            // Réponse
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
            break;
            
        default:
            throw new Exception('Action non valide');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
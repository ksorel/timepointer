<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';

// Vérification authentification
AuthMiddleware::verify();

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Récupération des employés actifs
    $stmt = $pdo->query("SELECT id, name, email, role FROM employees WHERE active = 1");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $employees
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
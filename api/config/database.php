<?php
class Database {
    private static $instance;
    private $connection;

    private function __construct() {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8',
            DB_HOST,
            DB_PORT,  // <-- Port explicitement inclus
            DB_NAME
        );

        $this->connection = new PDO(
            $dsn,
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,  // Important pour la sécurité
                PDO::ATTR_PERSISTENT => false         // Meilleure gestion des connexions
            ]
        );
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Configuration des headers (fusionné depuis headers.php)
header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGINS);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Constantes de configuration (avec port explicite)
define('DB_HOST', '148.113.8.177');
define('DB_PORT', 3306);  // <-- Port MySQL par défaut (à adapter si nécessaire)
define('DB_NAME', 'timepointer');
define('DB_USER', 'intersante');
define('DB_PASSWORD', '    private $password = "1Nters@nt2saasesi";');
// Définition des origines autorisées pour CORS
// Vous pouvez remplacer 'https://votre-domaine.com' par l'URL de votre application front-end
define('ALLOWED_ORIGINS', $_SERVER['HTTP_ORIGIN'] ?? 'https://votre-domaine.com');
<?php
require_once __DIR__ . '/../utils/Response.php';

class Employee {
    private $conn;
    private $table = 'employes';

    public $id;
    public $matricule;
    public $nom;
    public $prenom;
    public $email;
    public $password;
    public $token;
    public $token_expire;
    public $date_embauche;
    public $actif;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $query = "SELECT id, matricule, nom, prenom, password FROM " . $this->table . " 
                  WHERE matricule = :matricule AND actif = 1 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule', $this->matricule);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            Response::error('Matricule ou mot de passe incorrect');
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $row['id'];
        $this->nom = $row['nom'];
        $this->prenom = $row['prenom'];

        if (!password_verify($this->password, $row['password'])) {
            Response::error('Matricule ou mot de passe incorrect');
        }

        // Générer un token
        $this->token = bin2hex(random_bytes(32));
        $this->token_expire = date('Y-m-d H:i:s', strtotime('+1 day'));

        // Mettre à jour le token dans la base
        $this->updateToken();

        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'token' => $this->token,
            'token_expire' => $this->token_expire
        ];
    }

    private function updateToken() {
        $query = "UPDATE " . $this->table . " 
                  SET token = :token, token_expire = :token_expire 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $this->token);
        $stmt->bindParam(':token_expire', $this->token_expire);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
    }

    public function validateToken() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE token = :token AND token_expire > NOW() LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $this->token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            return true;
        }

        return false;
    }

    public function getProfile() {
        $query = "SELECT id, matricule, nom, prenom, email, date_embauche 
                  FROM " . $this->table . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
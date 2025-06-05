<?php
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';

// EmployeeController is responsible for handling requests related to employees
// It allows administrators to create, update, and retrieve employee profiles,
class EmployeeController {
    private $db;
    private $employee;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->employee = new Employee($this->db);
    }

    /**
     * Créer un nouvel employé (réservé aux administrateurs)
     */
    public function createEmployee() {
        // Seul un admin peut créer des employés
        $this->checkAdminAccess();

        $data = json_decode(file_get_contents("php://input"));

        // Validation des données
        if (empty($data->matricule) || empty($data->nom) || empty($data->prenom)) {
            Response::error('Matricule, nom et prénom sont obligatoires');
        }

        $this->employee->matricule = $data->matricule;
        $this->employee->nom = $data->nom;
        $this->employee->prenom = $data->prenom;
        $this->employee->email = $data->email ?? null;
        $this->employee->date_embauche = $data->date_embauche ?? date('Y-m-d');
        $this->employee->password = password_hash($data->password ?? 'password123', PASSWORD_BCRYPT);

        $query = "INSERT INTO employes 
                  (matricule, nom, prenom, email, date_embauche, password) 
                  VALUES 
                  (:matricule, :nom, :prenom, :email, :date_embauche, :password)";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':matricule', $this->employee->matricule);
        $stmt->bindParam(':nom', $this->employee->nom);
        $stmt->bindParam(':prenom', $this->employee->prenom);
        $stmt->bindParam(':email', $this->employee->email);
        $stmt->bindParam(':date_embauche', $this->employee->date_embauche);
        $stmt->bindParam(':password', $this->employee->password);

        if ($stmt->execute()) {
            Response::success('Employé créé avec succès', [
                'id' => $this->db->lastInsertId(),
                'matricule' => $this->employee->matricule,
                'nom' => $this->employee->nom,
                'prenom' => $this->employee->prenom
            ]);
        } else {
            Response::error('Erreur lors de la création de l\'employé');
        }
    }

    /**
     * Lister tous les employés (réservé aux administrateurs)
     */
    public function getAllEmployees() {
        $this->checkAdminAccess();

        $query = "SELECT id, matricule, nom, prenom, email, date_embauche, actif 
                  FROM employes 
                  ORDER BY nom, prenom";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success('Liste des employés récupérée', $employees);
    }

    /**
     * Récupérer les détails d'un employé
     */
    public function getEmployee($id) {
        $current_user_id = AuthMiddleware::authenticate();
        
        // Seul l'admin ou l'employé lui-même peut voir les détails
        if (!$this->isAdmin($current_user_id)) {
            $this->employee->id = $current_user_id;
            $profile = $this->employee->getProfile();
            
            if ($profile['id'] != $id) {
                Response::error('Accès non autorisé', 403);
            }
        }

        $this->employee->id = $id;
        $employee = $this->employee->getProfile();

        if ($employee) {
            Response::success('Employé récupéré', $employee);
        } else {
            Response::error('Employé non trouvé', 404);
        }
    }

    /**
     * Mettre à jour un employé
     */
    public function updateEmployee($id) {
        $current_user_id = AuthMiddleware::authenticate();
        
        // Seul l'admin peut modifier d'autres employés
        if (!$this->isAdmin($current_user_id) && $current_user_id != $id) {
            Response::error('Accès non autorisé', 403);
        }

        $data = json_decode(file_get_contents("php://input"));

        $this->employee->id = $id;
        $current_data = $this->employee->getProfile();

        if (!$current_data) {
            Response::error('Employé non trouvé', 404);
        }

        $query = "UPDATE employes SET 
                  nom = :nom,
                  prenom = :prenom,
                  email = :email,
                  actif = :actif
                  WHERE id = :id";

        // Seul l'admin peut changer le matricule
        if ($this->isAdmin($current_user_id)) {
            $query = "UPDATE employes SET 
                      matricule = :matricule,
                      nom = :nom,
                      prenom = :prenom,
                      email = :email,
                      actif = :actif
                      WHERE id = :id";
        }

        $stmt = $this->db->prepare($query);

        // Si l'admin met à jour, il peut changer le matricule
        // Sinon, on garde le matricule actuel
        if ($this->isAdmin($current_user_id)) {
            $stmt->bindParam(':matricule', $data->matricule ?? $current_data['matricule']);
        }
        
        $stmt->bindParam(':nom', $data->nom ?? $current_data['nom']);
        $stmt->bindParam(':prenom', $data->prenom ?? $current_data['prenom']);
        $stmt->bindParam(':email', $data->email ?? $current_data['email']);
        $stmt->bindParam(':actif', $data->actif ?? $current_data['actif']);
        $stmt->bindParam(':id', $id);

        // Exécuter la requête
        if ($stmt->execute()) {
            Response::success('Employé mis à jour avec succès');
        } else {
            Response::error('Erreur lors de la mise à jour de l\'employé');
        }
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword($id) {
        $current_user_id = AuthMiddleware::authenticate();
        
        // Seul l'employé lui-même peut changer son mot de passe
        if ($current_user_id != $id) {
            Response::error('Accès non autorisé', 403);
        }

        $data = json_decode(file_get_contents("php://input"));

        // Vérifier que les champs sont remplis
        if (empty($data->current_password) || empty($data->new_password)) {
            Response::error('Mot de passe actuel et nouveau mot de passe sont obligatoires');
        }

        // Vérifier l'ancien mot de passe
        $query = "SELECT password FROM employes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si l'employé n'existe pas
        if (!password_verify($data->current_password, $result['password'])) {
            Response::error('Mot de passe actuel incorrect');
        }

        // Mettre à jour avec le nouveau mot de passe
        $new_password_hash = password_hash($data->new_password, PASSWORD_BCRYPT);
        
        $update_query = "UPDATE employes SET password = :password WHERE id = :id";
        $update_stmt = $this->db->prepare($update_query);
        $update_stmt->bindParam(':password', $new_password_hash);
        $update_stmt->bindParam(':id', $id);

        // Exécuter la mise à jour
        if ($update_stmt->execute()) {
            Response::success('Mot de passe changé avec succès');
        } else {
            Response::error('Erreur lors du changement de mot de passe');
        }
    }

    /**
     * Vérifier si l'utilisateur est admin
     * (À adapter selon votre logique métier)
     */
    private function isAdmin($employee_id) {
        // Implémentation basique - à adapter
        // Par exemple, vérifier un champ 'is_admin' dans la table employes
        return false; // Temporaire - à implémenter
    }

    /**
     * Vérifier l'accès admin
     */
    private function checkAdminAccess() {
        $current_user_id = AuthMiddleware::authenticate();
        // Seul un admin peut accéder à cette méthode      
        if (!$this->isAdmin($current_user_id)) {
            Response::error('Accès réservé aux administrateurs', 403);
        }
    }
}
?>
<?php
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/Response.php';

// AuthController is responsible for handling authentication-related requests
class AuthController {
    private $db;
    private $employee;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->employee = new Employee($this->db);
    }

    /**
     * Handles user login by validating credentials and returning user data.
     * Expects JSON input with 'matricule' and 'password'.
     */
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->matricule) && !empty($data->password)) {
            $this->employee->matricule = $data->matricule;
            $this->employee->password = $data->password;

            $result = $this->employee->login();
            Response::success('Connexion réussie', $result);
        } else {
            Response::error('Matricule et mot de passe requis');
        }
    }
    /**
     * Retrieves the profile of the authenticated employee.
     * Uses AuthMiddleware to ensure the user is authenticated.
     */
    public function profile() {
        $employee_id = AuthMiddleware::authenticate();
        
        $this->employee->id = $employee_id;
        $profile = $this->employee->getProfile();
        
        Response::success('Profil récupéré', $profile);
    }
}
?>
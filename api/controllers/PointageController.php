<?php
require_once __DIR__ . '/../models/Pointage.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';

// PointageController is responsible for handling pointage-related requests
// It allows employees to register their pointage, retrieve today's pointage, and view their historical pointages.
class PointageController {
    private $db;
    private $pointage;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->pointage = new Pointage($this->db);
    }
    /**
     * Enregistre le pointage de l'employé pour la journée en cours.
     * Expects JSON input with fields for morning and afternoon arrival/departure times and an optional comment.
     */
    public function enregistrerPointage() {
        $employee_id = AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents("php://input"));

        $this->pointage->employe_id = $employee_id;
        $this->pointage->date_pointage = date('Y-m-d');
        
        if (!empty($data->heure_arrivee_matin)) {
            $this->pointage->heure_arrivee_matin = $data->heure_arrivee_matin;
        }
        
        if (!empty($data->heure_depart_matin)) {
            $this->pointage->heure_depart_matin = $data->heure_depart_matin;
        }
        
        if (!empty($data->heure_arrivee_aprem)) {
            $this->pointage->heure_arrivee_aprem = $data->heure_arrivee_aprem;
        }
        
        if (!empty($data->heure_depart_aprem)) {
            $this->pointage->heure_depart_aprem = $data->heure_depart_aprem;
        }
        
        if (!empty($data->commentaire)) {
            $this->pointage->commentaire = $data->commentaire;
        }

        if ($this->pointage->create()) {
            Response::success('Pointage enregistré avec succès');
        }
    }
    /**
     * Récupère le pointage du jour pour l'employé authentifié.
     * Utilise AuthMiddleware pour s'assurer que l'utilisateur est authentifié.
     */
    public function getPointageDuJour() {
        $employee_id = AuthMiddleware::authenticate();

        $this->pointage->employe_id = $employee_id;
        $this->pointage->date_pointage = date('Y-m-d');

        $pointage = $this->pointage->getToday();
        Response::success('Pointage du jour récupéré', $pointage);
    }
    /**
     * Récupère l'historique des pointages de l'employé pour une période donnée.
     * Expects query parameters 'start_date' and 'end_date' (format: YYYY-MM-DD).
     */
    public function getHistorique() {
        $employee_id = AuthMiddleware::authenticate();
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');

        $this->pointage->employe_id = $employee_id;
        $historique = $this->pointage->getHistory($start_date, $end_date);

        Response::success('Historique récupéré', $historique);
    }
}
?>
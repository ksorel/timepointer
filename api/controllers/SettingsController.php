<?php
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';

// SettingsController is responsible for handling requests related to application settings
// It allows retrieval and updating of operating hours (horaires) for the application.
class SettingsController {
    private $db;
    private $settings;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->settings = new Settings($this->db);
    }
    /**
     * Retrieves the operating hours (horaires) of the application.
     * Uses AuthMiddleware to ensure the user is authenticated.
     */
    public function getHoraires() {
        AuthMiddleware::authenticate();
        
        $horaires = $this->settings->getHoraires();
        Response::success('Horaires récupérés', $horaires);
    }
    /**
     * Retrieves the operating hours for the current day.
     * Uses AuthMiddleware to ensure the user is authenticated.
     */
    public function getHoraireDuJour() {
        AuthMiddleware::authenticate();
        
        $day = date('N'); // 1 (lundi) à 7 (dimanche)
        $horaire = $this->settings->getHoraireByDay($day);
        
        Response::success('Horaire du jour récupéré', $horaire);
    }
    /**
     * Updates the operating hours (horaires) of the application.
     * Expects JSON input with an array of horaires for each day of the week.
     * Uses AuthMiddleware to ensure the user is authenticated.
     */
    public function updateHoraires() {
        AuthMiddleware::authenticate();
        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->settings->updateHoraires($data)) {
            Response::success('Horaires mis à jour avec succès');
        } else {
            Response::error('Erreur lors de la mise à jour des horaires');
        }
    }
}
?>
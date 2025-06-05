<?php
require_once __DIR__ . '/../utils/Response.php';

class Pointage {
    private $conn;
    private $table = 'pointages';

    public $id;
    public $employe_id;
    public $date_pointage;
    public $heure_arrivee_matin;
    public $heure_depart_matin;
    public $heure_arrivee_aprem;
    public $heure_depart_aprem;
    public $commentaire;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        // Vérifier si un pointage existe déjà pour cette date
        if ($this->pointageExists()) {
            return $this->update();
        }

        $query = "INSERT INTO " . $this->table . " 
                  SET employe_id = :employe_id, 
                      date_pointage = :date_pointage,
                      heure_arrivee_matin = :heure_arrivee_matin,
                      heure_depart_matin = :heure_depart_matin,
                      heure_arrivee_aprem = :heure_arrivee_aprem,
                      heure_depart_aprem = :heure_depart_aprem,
                      commentaire = :commentaire";

        $stmt = $this->conn->prepare($query);

        $this->date_pointage = date('Y-m-d');

        $stmt->bindParam(':employe_id', $this->employe_id);
        $stmt->bindParam(':date_pointage', $this->date_pointage);
        $stmt->bindParam(':heure_arrivee_matin', $this->heure_arrivee_matin);
        $stmt->bindParam(':heure_depart_matin', $this->heure_depart_matin);
        $stmt->bindParam(':heure_arrivee_aprem', $this->heure_arrivee_aprem);
        $stmt->bindParam(':heure_depart_aprem', $this->heure_depart_aprem);
        $stmt->bindParam(':commentaire', $this->commentaire);

        if ($stmt->execute()) {
            return true;
        }

        Response::error('Erreur lors de l\'enregistrement du pointage');
    }

    private function pointageExists() {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE employe_id = :employe_id AND date_pointage = :date_pointage";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employe_id', $this->employe_id);
        $stmt->bindParam(':date_pointage', $this->date_pointage);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function update() {
        $query = "UPDATE " . $this->table . " 
                  SET heure_arrivee_matin = COALESCE(:heure_arrivee_matin, heure_arrivee_matin),
                      heure_depart_matin = COALESCE(:heure_depart_matin, heure_depart_matin),
                      heure_arrivee_aprem = COALESCE(:heure_arrivee_aprem, heure_arrivee_aprem),
                      heure_depart_aprem = COALESCE(:heure_depart_aprem, heure_depart_aprem),
                      commentaire = :commentaire
                  WHERE employe_id = :employe_id AND date_pointage = :date_pointage";

        $stmt = $this->conn->prepare($query);

        $this->date_pointage = date('Y-m-d');

        $stmt->bindParam(':employe_id', $this->employe_id);
        $stmt->bindParam(':date_pointage', $this->date_pointage);
        $stmt->bindParam(':heure_arrivee_matin', $this->heure_arrivee_matin);
        $stmt->bindParam(':heure_depart_matin', $this->heure_depart_matin);
        $stmt->bindParam(':heure_arrivee_aprem', $this->heure_arrivee_aprem);
        $stmt->bindParam(':heure_depart_aprem', $this->heure_depart_aprem);
        $stmt->bindParam(':commentaire', $this->commentaire);

        if ($stmt->execute()) {
            return true;
        }

        Response::error('Erreur lors de la mise à jour du pointage');
    }

    public function getToday() {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE employe_id = :employe_id AND date_pointage = :date_pointage";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employe_id', $this->employe_id);
        $stmt->bindParam(':date_pointage', $this->date_pointage);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistory($start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE employe_id = :employe_id 
                  AND date_pointage BETWEEN :start_date AND :end_date
                  ORDER BY date_pointage DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employe_id', $this->employe_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
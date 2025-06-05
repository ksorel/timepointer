<?php
require_once __DIR__ . '/../utils/Response.php';

class Settings {
    private $conn;
    private $table = 'horaires';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getHoraires() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY jour_semaine";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHoraireByDay($day) {
        $query = "SELECT * FROM " . $this->table . " WHERE jour_semaine = :day LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':day', $day);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateHoraires($data) {
        // Supprimer tous les horaires existants
        $this->conn->query("TRUNCATE TABLE " . $this->table);

        $query = "INSERT INTO " . $this->table . " 
                  (jour_semaine, heure_debut_matin, heure_fin_matin, heure_debut_aprem, heure_fin_aprem) 
                  VALUES (:jour, :debut_matin, :fin_matin, :debut_aprem, :fin_aprem)";

        $stmt = $this->conn->prepare($query);

        foreach ($data as $horaire) {
            $stmt->bindParam(':jour', $horaire['jour_semaine']);
            $stmt->bindParam(':debut_matin', $horaire['heure_debut_matin']);
            $stmt->bindParam(':fin_matin', $horaire['heure_fin_matin']);
            $stmt->bindParam(':debut_aprem', $horaire['heure_debut_aprem']);
            $stmt->bindParam(':fin_aprem', $horaire['heure_fin_aprem']);
            $stmt->execute();
        }

        return true;
    }
}
?>
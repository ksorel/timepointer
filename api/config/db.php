<?php
class Database {
    private $host = "148.113.8.177";
    private $port = "3306";
    private $db_name = "timepointer";
    private $username = "intersante";
    private $password = "1Nters@nt2saasesi";
    public $conn;

    // Get the database connection
    // This method establishes a connection to the database using PDO
    public function getConnection() {
        // Ensure the connection is closed before creating a new one
        $this->conn = null;

        // Create a new PDO instance
        // The connection string includes the host, port, database name, and character set
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . "port=".$this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }
}

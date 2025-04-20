<?php
// db.php
class Database {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('sqlite:deeppink.db');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                url TEXT NOT NULL,
                report_html TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(user_id) REFERENCES users(id)
            )
        ");

        // Check if initial user exists
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'Danielcreux'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $this->pdo->exec("
                INSERT INTO users (username, password, name, email)
                VALUES ('Danielcreux', '".password_hash('Danielcreux', PASSWORD_DEFAULT)."', 'Joshué Daniel Freire Sánchez', 'info@freire-sanchez-valencia.es')
            ");
        }
    }

    public function getPDO() {
        return $this->pdo;
    }
}
?>
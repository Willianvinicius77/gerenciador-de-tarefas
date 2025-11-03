<?php
    declare(strict_types=1);

    class Database {
        private static $instance = null;
        private $pdo;

        private function __construct() {
            $dataDir = __DIR__ . '/../data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            $dbFile = $dataDir . '/database.sqlite';
            $needInit = !file_exists($dbFile);

            $this->pdo = new PDO('sqlite:' . $dbFile);
            // Setar modo de erro
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($needInit) {
                $this->init();
            }
        }

        private function init() {
            $sql = <<<SQL
    CREATE TABLE IF NOT EXISTS tarefas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        descricao TEXT,
        concluida INTEGER NOT NULL DEFAULT 0,
        prioridade INTEGER NOT NULL DEFAULT 3,
        criado_em TEXT NOT NULL
    );
    SQL;
            $this->pdo->exec($sql);
        }

        public static function getInstance(): PDO {
            if (self::$instance === null) {
                $db = new Database();
                self::$instance = $db->pdo;
            }
            return self::$instance;
        }
    }
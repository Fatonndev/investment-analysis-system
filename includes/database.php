<?php
require_once dirname(__DIR__) . '/config.php';

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
        $this->createTables();
    }
    
    private function connect() {
        try {
            // Use SQLite instead of MySQL for compatibility
            $dbPath = __DIR__ . '/../app.db';
            $this->connection = new PDO("sqlite:$dbPath");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            $this->connection->exec("PRAGMA foreign_keys = ON;");
        } catch (PDOException $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $tables = [
            "projects" => "CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )",
            
            "production_data" => "CREATE TABLE IF NOT EXISTS production_data (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER,
                period TEXT,
                product_type TEXT,
                quantity REAL,
                unit TEXT,
                revenue REAL,
                variable_costs REAL,
                fixed_costs REAL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "operational_costs" => "CREATE TABLE IF NOT EXISTS operational_costs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER,
                period TEXT,
                cost_type TEXT CHECK(cost_type IN ('raw_material', 'energy', 'logistics', 'labor', 'depreciation')) NOT NULL,
                -- Common fields
                cost REAL,
                total_cost REAL,
                -- Raw material specific fields
                material_type TEXT,
                cost_per_unit REAL,
                quantity_used REAL,
                -- Energy specific fields
                energy_type TEXT,
                -- Logistics specific fields
                route TEXT,
                -- Labor specific fields
                department TEXT,
                salary_cost REAL,
                benefits REAL,
                -- Depreciation specific fields
                asset_name TEXT,
                depreciation_amount REAL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "product_prices" => "CREATE TABLE IF NOT EXISTS product_prices (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER,
                period TEXT,
                product_type TEXT,
                size_spec TEXT,
                precision_class TEXT,
                region TEXT,
                price_per_unit REAL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "investment_data" => "CREATE TABLE IF NOT EXISTS investment_data (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER,
                investment_type TEXT,
                description TEXT,
                amount REAL,
                investment_date TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "product_types" => "CREATE TABLE IF NOT EXISTS product_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                description TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables as $sql) {
            $this->connection->exec($sql);
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function executeQuery($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $data = $this->fetchAll($sql, $params);
        return $data ? $data[0] : null;
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($data);
        
        return $this->connection->lastInsertId();
    }
}
?>
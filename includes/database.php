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
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            $this->connection->set_charset("utf8");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        $tables = [
            "projects" => "CREATE TABLE IF NOT EXISTS projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            "production_data" => "CREATE TABLE IF NOT EXISTS production_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT,
                period DATE,
                product_type VARCHAR(100),
                quantity DECIMAL(15,2),
                unit VARCHAR(20),
                revenue DECIMAL(15,2),
                variable_costs DECIMAL(15,2),
                fixed_costs DECIMAL(15,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "operational_costs" => "CREATE TABLE IF NOT EXISTS operational_costs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT,
                period DATE,
                cost_type ENUM('raw_material', 'energy', 'logistics', 'labor', 'depreciation') NOT NULL,
                -- Common fields
                cost DECIMAL(15,2),
                total_cost DECIMAL(15,2),
                -- Raw material specific fields
                material_type VARCHAR(100),
                cost_per_unit DECIMAL(10,2),
                quantity_used DECIMAL(15,2),
                -- Energy specific fields
                energy_type VARCHAR(100),
                -- Logistics specific fields
                route VARCHAR(100),
                -- Labor specific fields
                department VARCHAR(100),
                salary_cost DECIMAL(15,2),
                benefits DECIMAL(15,2),
                -- Depreciation specific fields
                asset_name VARCHAR(100),
                depreciation_amount DECIMAL(15,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "product_prices" => "CREATE TABLE IF NOT EXISTS product_prices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT,
                period DATE,
                product_type VARCHAR(100),
                size_spec VARCHAR(100),
                precision_class VARCHAR(50),
                region VARCHAR(100),
                price_per_unit DECIMAL(10,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "investment_data" => "CREATE TABLE IF NOT EXISTS investment_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                project_id INT,
                investment_type VARCHAR(100),
                description TEXT,
                amount DECIMAL(15,2),
                investment_date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
            )",
            
            "product_types" => "CREATE TABLE IF NOT EXISTS product_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables as $table_name => $sql) {
            if (!$this->connection->query($sql)) {
                die("Error creating table $table_name: " . $this->connection->error);
            }
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function executeQuery($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Default to string
            // Try to determine types based on parameter values
            $param_types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $param_types .= 'i';
                } elseif (is_float($param) || is_double($param)) {
                    $param_types .= 'd';
                } else {
                    $param_types .= 's';
                }
            }
            $types = $param_types;
            
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->executeQuery($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function fetchOne($sql, $params = []) {
        $data = $this->fetchAll($sql, $params);
        return $data ? $data[0] : null;
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->connection->prepare($sql);
        $values = array_values($data);
        $types = str_repeat('s', count($values));
        
        // Determine parameter types
        $param_types = '';
        foreach ($values as $value) {
            if (is_int($value)) {
                $param_types .= 'i';
            } elseif (is_float($value) || is_double($value)) {
                $param_types .= 'd';
            } else {
                $param_types .= 's';
            }
        }
        
        $stmt->bind_param($param_types, ...$values);
        $stmt->execute();
        
        return $this->connection->insert_id;
    }
}
?>
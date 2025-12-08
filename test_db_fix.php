<?php
require_once 'config.php';
require_once 'includes/database.php';

try {
    $db = new Database();
    echo "Database connection successful!\n";
    
    // Check if product types table exists and has data
    $tableCheck = $db->fetchOne("SHOW TABLES LIKE 'product_types'");
    if (!$tableCheck) {
        echo "Table 'product_types' does not exist. The database migration may not have run yet.\n";
        exit;
    }

    // Check if any product types already exist
    $existingTypes = $db->fetchAll("SELECT * FROM product_types");
    if (empty($existingTypes)) {
        // Insert default product types
        $defaultTypes = [
            ['name' => 'Стальные трубы', 'description' => 'Основная продукция трубопрокатного завода'],
            ['name' => 'Трубная заготовка', 'description' => 'Полуфабрикат для дальнейшей обработки'],
            ['name' => 'Оцинкованные трубы', 'description' => 'Трубы с защитным цинковым покрытием'],
            ['name' => 'Специальные трубы', 'description' => 'Трубы специального назначения']
        ];
        
        foreach ($defaultTypes as $type) {
            $db->insert('product_types', $type);
        }
        
        echo "Default product types added successfully.\n";
    } else {
        echo "Product types already exist in the database.\n";
    }

    // Test the fixed query by checking if the required tables exist
    $tables = ['production_data', 'raw_material_costs', 'energy_costs', 'logistics_costs', 'labor_costs', 'depreciation_costs'];
    
    foreach ($tables as $table) {
        $result = $db->fetchOne("SELECT COUNT(*) as count FROM information_schema.columns WHERE table_name = ? AND table_schema = ?", [$table, DB_NAME]);
        if ($result) {
            echo "Table $table exists\n";
        } else {
            echo "Table $table might not exist\n";
        }
    }
    
    // Test a simplified version of the query to make sure it works
    $testProjectId = 1;
    $sql = "
        SELECT 
            pd.period,
            SUM(pd.revenue) as total_revenue,
            SUM(pd.variable_costs + pd.fixed_costs) as total_costs
        FROM production_data pd
        WHERE pd.project_id = ?
        GROUP BY pd.period
        ORDER BY pd.period
    ";
    
    $result = $db->fetchAll($sql, [$testProjectId]);
    echo "Query test completed successfully. Found " . count($result) . " records.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
<?php
require_once 'config.php';
require_once 'includes/database.php';

try {
    $db = new Database();
    echo "Database connection successful!\n";
    
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
<?php
// Initialize the database with all tables including product_types
require_once 'config.php';
require_once 'includes/database.php';

echo "Initializing database with product_types table...\n";

try {
    $db = new Database();
    echo "Database connection successful!\n";
    echo "All tables created/verified successfully, including product_types table.\n";
    
    // Check if product_types table exists and populate with default values if empty
    $tableCheck = $db->fetchOne("SHOW TABLES LIKE 'product_types'");
    if ($tableCheck) {
        echo "Product types table exists.\n";
        
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
            echo "Product types already exist in the database (" . count($existingTypes) . " records).\n";
        }
    } else {
        echo "Error: Product types table was not created.\n";
    }
    
    echo "Database initialization completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
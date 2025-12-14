<?php
require_once 'includes/database.php';
require_once 'includes/calculations.php';

// header('Content-Type: application/json');

try {
    $db = new Database();
    $analysis = new InvestmentAnalysis();

    // Проверим, есть ли проекты в базе данных
    $projects = $db->fetchAll("SELECT * FROM projects");
    echo "Projects found: " . count($projects) . "\n";
    var_dump($projects);

    // Проверим, есть ли производственные данные
    $productionData = $db->fetchAll("SELECT * FROM production_data");
    echo "Production data found: " . count($productionData) . "\n";
    var_dump($productionData);

    // Проверим, есть ли инвестиционные данные
    $investmentData = $db->fetchAll("SELECT * FROM investment_data");
    echo "Investment data found: " . count($investmentData) . "\n";
    var_dump($investmentData);

    // Если есть хотя бы один проект, попробуем получить его анализ
    if (!empty($projects)) {
        $projectId = $projects[0]['id'];
        echo "Testing analysis for project ID: " . $projectId . "\n";
        
        $analysisResults = $analysis->calculateProjectAnalysis($projectId, 0.1, 3);
        
        echo "Analysis Results:\n";
        var_dump($analysisResults);
        
        // Выведем конкретно интересующие нас поля
        if (isset($analysisResults['period_investments_by_period'])) {
            echo "Period investments by period:\n";
            var_dump($analysisResults['period_investments_by_period']);
        }
        
        if (isset($analysisResults['period_revenues_by_period'])) {
            echo "Period revenues by period:\n";
            var_dump($analysisResults['period_revenues_by_period']);
        }
        
        if (isset($analysisResults['periods'])) {
            echo "Periods:\n";
            var_dump($analysisResults['periods']);
        }
    } else {
        echo "No projects found in database\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    var_dump($e);
}
?>
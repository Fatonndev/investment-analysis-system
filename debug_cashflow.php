<?php
require_once 'includes/database.php';
require_once 'includes/calculations.php';

// Remove JSON header for debugging
// header('Content-Type: application/json');

try {
    $db = new Database();
    $analysis = new InvestmentAnalysis();
    
    $projectId = $_GET['project_id'] ?? 1;
    $discountRate = isset($_GET['discount_rate']) ? floatval($_GET['discount_rate']) : 10;
    $forecastYears = isset($_GET['forecast_years']) ? intval($_GET['forecast_years']) : 3;
    
    echo "Debugging cash flow data retrieval:\n";
    echo "Project ID: " . $projectId . "\n";
    echo "Discount Rate: " . $discountRate . "\n";
    echo "Forecast Years: " . $forecastYears . "\n";
    
    // Test getting raw financial data
    $financialData = $analysis->getProjectFinancialData($projectId);
    echo "Financial Data Count: " . count($financialData) . "\n";
    echo "Financial Data:\n";
    print_r($financialData);
    
    // Test getting investment data
    $investmentData = $analysis->getProjectInvestmentData($projectId);
    echo "Investment Data Count: " . count($investmentData) . "\n";
    echo "Investment Data:\n";
    print_r($investmentData);
    
    // Run the full analysis
    $analysisResults = $analysis->calculateProjectAnalysis($projectId, $discountRate / 100, $forecastYears);
    
    if (isset($analysisResults['error'])) {
        echo "Error in analysis: " . $analysisResults['error'] . "\n";
    }
    
    echo "Analysis Results:\n";
    var_dump($analysisResults);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
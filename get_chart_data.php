<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/calculations.php';

try {
    $projectId = $_POST['project_id'] ?? $_GET['project_id'] ?? 0;
    $discountRate = isset($_POST['discount_rate']) ? floatval($_POST['discount_rate']) : (isset($_GET['discount_rate']) ? floatval($_GET['discount_rate']) : 10);
    $forecastYears = isset($_POST['forecast_years']) ? intval($_POST['forecast_years']) : (isset($_GET['forecast_years']) ? intval($_GET['forecast_years']) : 3);

    if ($projectId == 0) {
        throw new Exception("Project ID is required");
    }

    $analysis = new InvestmentAnalysis();
    $analysisResults = $analysis->calculateProjectAnalysis($projectId, $discountRate / 100, $forecastYears);

    if (isset($analysisResults['error'])) {
        throw new Exception($analysisResults['error']);
    }

    echo json_encode([
        'success' => true,
        'period_investments_by_period' => $analysisResults['period_investments_by_period'],
        'period_revenues_by_period' => $analysisResults['period_revenues_by_period'],
        'periods' => $analysisResults['periods'],
        'months_count' => $analysisResults['months_count']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
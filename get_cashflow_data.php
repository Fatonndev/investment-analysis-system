<?php
require_once 'includes/database.php';
require_once 'includes/calculations.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = new Database();
    $analysis = new InvestmentAnalysis();
    
    $projectId = $_GET['project_id'] ?? $_POST['project_id'] ?? null;
    $discountRate = isset($_GET['discount_rate']) ? floatval($_GET['discount_rate']) : 10;
    $forecastYears = isset($_GET['forecast_years']) ? intval($_GET['forecast_years']) : 3;
    
    if (!$projectId) {
        throw new Exception('Project ID is required');
    }
    
    // Получаем данные анализа проекта
    $analysisResults = $analysis->calculateProjectAnalysis($projectId, $discountRate / 100, $forecastYears);
    
    if (isset($analysisResults['error'])) {
        throw new Exception($analysisResults['error']);
    }
    
    // Подготавливаем данные для графика
    $labels = [];
    $investmentData = [];
    $revenueData = [];
    
    // Используем месячные данные из результатов анализа
    $periodInvestments = $analysisResults['period_investments_by_period'];
    $periodRevenues = $analysisResults['period_revenues_by_period'];
    $periods = $analysisResults['periods'];
    
    // Обрабатываем все периоды с соответствующими инвестициями и доходами
    for ($i = 0; $i < count($periodRevenues); $i++) {
        // Используем период из базы данных или просто номер месяца
        $labels[] = isset($periods[$i]) ? $periods[$i] : 'Месяц ' . ($i + 1);
        
        // Добавляем инвестиции как отрицательные значения (для красных столбцов вниз)
        $investmentValue = isset($periodInvestments[$i]) ? $periodInvestments[$i] : 0;
        $investmentData[] = -abs($investmentValue); // Убедиться, что значение отрицательное
        
        // Добавляем доходы как положительные значения (для зеленых столбцов вверх)
        $revenueValue = isset($periodRevenues[$i]) ? $periodRevenues[$i] : 0;
        $revenueData[] = max(0, $revenueValue);
    }
    
    // Формируем ответ
    $response = [
        'success' => true,
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Доходы (руб.)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Инвестиции (руб.)',
                    'data' => $investmentData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]
            ]
        ],
        'analysis_results' => $analysisResults
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
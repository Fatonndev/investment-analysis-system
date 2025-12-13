<?php
require_once dirname(__FILE__) . '/includes/calculations.php';

// Initialize the calculator
$calculator = new InvestmentAnalysis();

// Test with a sample project ID (using 1 as example)
$projectId = 1;
$discountRate = 0.1;  // 10%
$forecastYears = 3;

echo "Testing calculateProjectAnalysis function...\n";
echo "Project ID: $projectId, Discount Rate: {$discountRate}%, Forecast Years: $forecastYears\n";

try {
    $result = $calculator->calculateProjectAnalysis($projectId, $discountRate, $forecastYears);
    
    if (isset($result['error'])) {
        echo "Error: " . $result['error'] . "\n";
    } else {
        echo "Calculation successful!\n";
        echo "ROI: " . round($result['roi'], 2) . "%\n";
        echo "NPV: " . round($result['npv'], 2) . "\n";
        if ($result['irr'] !== null) {
            echo "IRR: " . round($result['irr'] * 100, 2) . "%\n";
        } else {
            echo "IRR: Cannot be calculated\n";
        }
        echo "Months Count: " . $result['months_count'] . "\n";
        echo "Monthly Period Revenues: ";
        print_r(array_slice($result['period_revenues_by_period'], 0, 12)); // Show first 12 months
    }
} catch (Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
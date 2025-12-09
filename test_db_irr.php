<?php
require_once __DIR__ . '/includes/calculations.php';

// Test the IRR calculation with database data
$analysis = new InvestmentAnalysis();

// Get project analysis which includes IRR
$result = $analysis->calculateProjectAnalysis(1);

if (isset($result['error'])) {
    echo "Error: " . $result['error'] . "\n";
} else {
    echo "Project Analysis Results:\n";
    echo "========================\n";
    echo "Total Revenue: " . $result['total_revenue'] . "\n";
    echo "Total Costs: " . $result['total_costs'] . "\n";
    echo "Total Profit: " . $result['total_profit'] . "\n";
    echo "Total Investment: " . $result['total_investment'] . "\n";
    echo "ROI: " . number_format($result['roi'], 2) . "%\n";
    echo "NPV: " . number_format($result['npv'], 2) . "\n";
    echo "IRR: " . number_format($result['irr'] * 100, 2) . "%\n";
    echo "Payback Period: " . $result['payback_period'] . "\n";
    
    echo "\nCash flows used for IRR calculation:\n";
    // We need to get the cash flows to display them
    $financialData = $analysis->getProjectFinancialData(1);
    $investmentData = $analysis->getProjectInvestmentData(1);
    
    echo "Financial data periods: \n";
    foreach ($financialData as $row) {
        echo "  Period: " . $row['period'] . ", Revenue: " . $row['total_revenue'] . ", Costs: " . $row['total_costs'] . "\n";
    }
    
    echo "\nInvestment data: \n";
    foreach ($investmentData as $investment) {
        echo "  Date: " . $investment['investment_date'] . ", Amount: " . $investment['amount'] . "\n";
    }
}
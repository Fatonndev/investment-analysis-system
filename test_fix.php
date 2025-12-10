<?php
require_once __DIR__ . '/includes/calculations.php';

// Create an instance of InvestmentAnalysis
$analysis = new InvestmentAnalysis();

// Test IRR calculation with a simple example
// Initial investment of -1000, followed by cash inflows of 300, 350, 400, 450
$testCashFlows = [-1000, 300, 350, 400, 450];

echo "Testing IRR calculation:\n";
echo "Cash flows: " . implode(", ", $testCashFlows) . "\n";

$irr = $analysis->calculateIRR($testCashFlows);
echo "IRR: " . ($irr * 100) . "%\n";

echo "\nTesting Payback Period calculation:\n";
$payback = $analysis->calculatePaybackPeriod($testCashFlows);
if ($payback >= 0) {
    echo "Payback Period: " . $payback . " years\n";
} else {
    echo "Payback Period: Does not pay back\n";
}

// Test with another example
echo "\n--- Another test case ---\n";
$testCashFlows2 = [-500, 200, 200, 200, 200];

echo "Cash flows: " . implode(", ", $testCashFlows2) . "\n";

$irr2 = $analysis->calculateIRR($testCashFlows2);
echo "IRR: " . ($irr2 * 100) . "%\n";

$payback2 = $analysis->calculatePaybackPeriod($testCashFlows2);
if ($payback2 >= 0) {
    echo "Payback Period: " . $payback2 . " years\n";
} else {
    echo "Payback Period: Does not pay back\n";
}

// Test with example that doesn't pay back
echo "\n--- Test case that doesn't pay back ---\n";
$testCashFlows3 = [-1000, 100, 100, 100];

echo "Cash flows: " . implode(", ", $testCashFlows3) . "\n";

$irr3 = $analysis->calculateIRR($testCashFlows3);
echo "IRR: " . ($irr3 * 100) . "%\n";

$payback3 = $analysis->calculatePaybackPeriod($testCashFlows3);
if ($payback3 >= 0) {
    echo "Payback Period: " . $payback3 . " years\n";
} else {
    echo "Payback Period: Does not pay back\n";
}
?>
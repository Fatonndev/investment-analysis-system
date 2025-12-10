<?php
// Test the main calculation functions from the actual calculations.php file
require_once 'includes/calculations.php';

// Create an instance of InvestmentAnalysis (without database connection)
class TestAnalysis extends InvestmentAnalysis {
    public function __construct() {
        // Don't call parent constructor to avoid database connection
    }
}

// Create an instance of the test class
$analysis = new TestAnalysis();

// Test IRR calculation with a simple example
// Initial investment of -1000, followed by cash inflows of 300, 350, 400, 450
$testCashFlows = [-1000, 300, 350, 400, 450];

echo "Testing IRR calculation from main file:\n";
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

// Test with a more complex example that should have a good IRR
echo "\n--- More complex test case ---\n";
$testCashFlows4 = [-10000, 3000, 4000, 5000, 6000];

echo "Cash flows: " . implode(", ", $testCashFlows4) . "\n";

$irr4 = $analysis->calculateIRR($testCashFlows4);
echo "IRR: " . ($irr4 * 100) . "%\n";

$payback4 = $analysis->calculatePaybackPeriod($testCashFlows4);
if ($payback4 >= 0) {
    echo "Payback Period: " . $payback4 . " years\n";
} else {
    echo "Payback Period: Does not pay back\n";
}

// Verify that NPV at calculated IRR is close to 0
echo "\n--- Verification for first test case ---\n";
$npvAtIrr = $analysis->calculateNPV($testCashFlows, $irr);
echo "NPV at calculated IRR: $npvAtIrr\n";
echo "This should be close to 0 (within precision bounds)\n";

// Test the NPV calculation with a known discount rate
$npvAt10Percent = $analysis->calculateNPV($testCashFlows, 0.10);
echo "\nNPV at 10% discount rate: $npvAt10Percent\n";

?>
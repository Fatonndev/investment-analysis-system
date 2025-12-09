<?php
require_once __DIR__ . '/includes/calculations.php';

// Test the IRR calculation with sample data
$analysis = new InvestmentAnalysis();

// Test case 1: Simple investment with known IRR
// Initial investment: -1000, Year 1: 400, Year 2: 500, Year 3: 600
$cashFlows1 = [-1000, 400, 500, 600];
$irr1 = $analysis->calculateIRR($cashFlows1);
echo "Test 1 - Cash flows: " . json_encode($cashFlows1) . "\n";
echo "Calculated IRR: " . number_format($irr1 * 100, 2) . "%\n";
echo "Expected IRR: ~30.73%\n\n";

// Test case 2: Another simple case
// Initial investment: -100, Year 1: 50, Year 2: 60, Year 3: 70
$cashFlows2 = [-100, 50, 60, 70];
$irr2 = $analysis->calculateIRR($cashFlows2);
echo "Test 2 - Cash flows: " . json_encode($cashFlows2) . "\n";
echo "Calculated IRR: " . number_format($irr2 * 100, 2) . "%\n";
echo "Expected IRR: ~32.74%\n\n";

// Test case 3: Zero IRR case (all positive cash flows)
$cashFlows3 = [100, 50, 60, 70];
$irr3 = $analysis->calculateIRR($cashFlows3);
echo "Test 3 - Cash flows: " . json_encode($cashFlows3) . "\n";
echo "Calculated IRR: " . number_format($irr3 * 100, 2) . "%\n";
echo "Expected IRR: 0% (no valid IRR)\n\n";

// Test case 4: Single cash flow
$cashFlows4 = [-1000];
$irr4 = $analysis->calculateIRR($cashFlows4);
echo "Test 4 - Cash flows: " . json_encode($cashFlows4) . "\n";
echo "Calculated IRR: " . number_format($irr4 * 100, 2) . "%\n";
echo "Expected IRR: 0% (insufficient data)\n\n";

// Test case 5: All negative cash flows
$cashFlows5 = [-100, -50, -60, -70];
$irr5 = $analysis->calculateIRR($cashFlows5);
echo "Test 5 - Cash flows: " . json_encode($cashFlows5) . "\n";
echo "Calculated IRR: " . number_format($irr5 * 100, 2) . "%\n";
echo "Expected IRR: 0% (no valid IRR)\n\n";

// Test case 6: Exactly zero IRR (return of capital only)
$cashFlows6 = [-100, 110];
$irr6 = $analysis->calculateIRR($cashFlows6);
echo "Test 6 - Cash flows: " . json_encode($cashFlows6) . "\n";
echo "Calculated IRR: " . number_format($irr6 * 100, 2) . "%\n";
echo "Expected IRR: 10% (return of capital with 10% interest)\n\n";

// Test case 7: Project that loses money (negative IRR)
$cashFlows7 = [-100, 50, 30, 20];
$irr7 = $analysis->calculateIRR($cashFlows7);
echo "Test 7 - Cash flows: " . json_encode($cashFlows7) . "\n";
echo "Calculated IRR: " . number_format($irr7 * 100, 2) . "%\n";
echo "Expected IRR: Negative (project loses money)\n\n";

// Test the NPV function to verify the IRR is correct
echo "\nVerifying IRR with NPV calculation:\n";
$npv1 = $analysis->calculateNPV($cashFlows1, $irr1);
echo "NPV at calculated IRR for test 1: " . $npv1 . " (should be close to 0)\n";
$npv2 = $analysis->calculateNPV($cashFlows2, $irr2);
echo "NPV at calculated IRR for test 2: " . $npv2 . " (should be close to 0)\n";
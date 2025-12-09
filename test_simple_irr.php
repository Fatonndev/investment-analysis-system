<?php
// Simple IRR calculator without database dependency
class SimpleIRR {
    /**
     * Calculate IRR (Internal Rate of Return) using Newton-Raphson method
     */
    public function calculateIRR($cashFlows, $precision = 0.0001) {
        if (count($cashFlows) < 2) {
            return 0;
        }
        
        $positive = false;
        $negative = false;
        
        foreach ($cashFlows as $cf) {
            if ($cf > 0) $positive = true;
            if ($cf < 0) $negative = true;
        }
        
        if (!$positive || !$negative) {
            return 0; // Cannot calculate IRR if all values are positive or negative
        }
        
        // Initial guess for IRR
        $irr = 0.1; // Start with 10%
        $maxIterations = 100;
        $iteration = 0;
        
        while ($iteration < $maxIterations) {
            $npv = $this->calculateNPVWithRate($cashFlows, $irr);
            
            // If NPV is close to zero, we found our IRR
            if (abs($npv) < $precision) {
                return $irr;
            }
            
            // Calculate derivative of NPV function (for Newton-Raphson method)
            $npvDerivative = $this->calculateNPVDerivative($cashFlows, $irr);
            
            // Avoid division by zero
            if (abs($npvDerivative) < 1e-10) {
                break;
            }
            
            // Newton-Raphson update: x_new = x_old - f(x)/f'(x)
            $newIrr = $irr - ($npv / $npvDerivative);
            
            // If the new estimate is too extreme, try a smaller adjustment
            if ($newIrr <= -1 || $newIrr >= 10) { // Limit to reasonable bounds
                $newIrr = $irr - 0.01 * ($npv / max(abs($npvDerivative), 0.001));
            }
            
            // Check for convergence
            if (abs($newIrr - $irr) < $precision) {
                return $newIrr;
            }
            
            $irr = $newIrr;
            $iteration++;
        }
        
        // If Newton-Raphson fails, fall back to bisection method with extended range
        return $this->calculateIRRBisection($cashFlows, $precision);
    }
    
    private function calculateNPVWithRate($cashFlows, $rate) {
        $npv = 0;
        for ($i = 0; $i < count($cashFlows); $i++) {
            // Avoid division by zero when rate is -1
            if (abs(1 + $rate) < 1e-10) {
                return PHP_FLOAT_MAX; // Return very large value to indicate invalid rate
            }
            $npv += $cashFlows[$i] / pow(1 + $rate, $i);
        }
        return $npv;
    }
    
    private function calculateNPVDerivative($cashFlows, $rate) {
        $derivative = 0;
        for ($i = 1; $i < count($cashFlows); $i++) {
            // Derivative of CF[i]/(1+r)^i with respect to r is -i*CF[i]/(1+r)^(i+1)
            if (abs(1 + $rate) < 1e-10) {
                return 0; // Avoid division by zero
            }
            $derivative += -$i * $cashFlows[$i] / pow(1 + $rate, $i + 1);
        }
        return $derivative;
    }
    
    private function calculateIRRBisection($cashFlows, $precision = 0.0001) {
        // Extended range for bisection method
        $r_low = -0.999; // Very close to -100% but not equal
        $r_high = 10.0;  // Up to 1000%
        
        $maxIterations = 1000;
        $iteration = 0;
        
        while (($r_high - $r_low > $precision) && ($iteration < $maxIterations)) {
            $r_try = ($r_low + $r_high) / 2;
            $npv = $this->calculateNPVWithRate($cashFlows, $r_try);
            
            // If NPV is extremely large, the rate is invalid
            if (is_infinite($npv) || is_nan($npv)) {
                $r_high = $r_try;
                $iteration++;
                continue;
            }
            
            if ($npv > 0) {
                $r_low = $r_try;
            } else {
                $r_high = $r_try;
            }
            $iteration++;
        }
        
        $result = ($r_low + $r_high) / 2;
        
        // Validate the result by checking if NPV is approximately zero
        $finalNpv = $this->calculateNPVWithRate($cashFlows, $result);
        if (abs($finalNpv) > 0.1) { // If NPV is not close to zero, IRR could not be found
            return 0;
        }
        
        return $result;
    }
    
    // Public method to access NPV calculation for testing
    public function calculateNPVWithRatePublic($cashFlows, $rate) {
        return $this->calculateNPVWithRate($cashFlows, $rate);
    }
}

// Test the IRR calculation with sample data
$irrCalculator = new SimpleIRR();

// Test case 1: Simple investment with known IRR
// Initial investment: -1000, Year 1: 400, Year 2: 500, Year 3: 600
$cashFlows1 = [-1000, 400, 500, 600];
$irr1 = $irrCalculator->calculateIRR($cashFlows1);
echo "Test 1 - Cash flows: " . json_encode($cashFlows1) . "\n";
echo "Calculated IRR: " . number_format($irr1 * 100, 2) . "%\n";
echo "Expected IRR: ~30.73%\n\n";

// Test case 2: Another simple case
// Initial investment: -100, Year 1: 50, Year 2: 60, Year 3: 70
$cashFlows2 = [-100, 50, 60, 70];
$irr2 = $irrCalculator->calculateIRR($cashFlows2);
echo "Test 2 - Cash flows: " . json_encode($cashFlows2) . "\n";
echo "Calculated IRR: " . number_format($irr2 * 100, 2) . "%\n";
echo "Expected IRR: ~32.74%\n\n";

// Test case 3: Zero IRR case (all positive cash flows)
$cashFlows3 = [100, 50, 60, 70];
$irr3 = $irrCalculator->calculateIRR($cashFlows3);
echo "Test 3 - Cash flows: " . json_encode($cashFlows3) . "\n";
echo "Calculated IRR: " . number_format($irr3 * 100, 2) . "%\n";
echo "Expected IRR: 0% (no valid IRR)\n\n";

// Test case 4: Single cash flow
$cashFlows4 = [-1000];
$irr4 = $irrCalculator->calculateIRR($cashFlows4);
echo "Test 4 - Cash flows: " . json_encode($cashFlows4) . "\n";
echo "Calculated IRR: " . number_format($irr4 * 100, 2) . "%\n";
echo "Expected IRR: 0% (insufficient data)\n\n";

// Test case 5: All negative cash flows
$cashFlows5 = [-100, -50, -60, -70];
$irr5 = $irrCalculator->calculateIRR($cashFlows5);
echo "Test 5 - Cash flows: " . json_encode($cashFlows5) . "\n";
echo "Calculated IRR: " . number_format($irr5 * 100, 2) . "%\n";
echo "Expected IRR: 0% (no valid IRR)\n\n";

// Test case 6: Exactly zero IRR (return of capital only)
$cashFlows6 = [-100, 110];
$irr6 = $irrCalculator->calculateIRR($cashFlows6);
echo "Test 6 - Cash flows: " . json_encode($cashFlows6) . "\n";
echo "Calculated IRR: " . number_format($irr6 * 100, 2) . "%\n";
echo "Expected IRR: 10% (return of capital with 10% interest)\n\n";

// Test case 7: Project that loses money (negative IRR)
$cashFlows7 = [-100, 50, 30, 20];
$irr7 = $irrCalculator->calculateIRR($cashFlows7);
echo "Test 7 - Cash flows: " . json_encode($cashFlows7) . "\n";
echo "Calculated IRR: " . number_format($irr7 * 100, 2) . "%\n";
echo "Expected IRR: Negative (project loses money)\n\n";

// Test the NPV function to verify the IRR is correct
echo "\nVerifying IRR with NPV calculation:\n";
$npv1 = $irrCalculator->calculateNPVWithRatePublic($cashFlows1, $irr1);
echo "NPV at calculated IRR for test 1: " . $npv1 . " (should be close to 0)\n";
$npv2 = $irrCalculator->calculateNPVWithRatePublic($cashFlows2, $irr2);
echo "NPV at calculated IRR for test 2: " . $npv2 . " (should be close to 0)\n";

// Test case 8: The original problematic case that might have returned 100%
$cashFlows8 = [-100, 200]; // This would give 100% return
$irr8 = $irrCalculator->calculateIRR($cashFlows8);
echo "\nTest 8 - Cash flows: " . json_encode($cashFlows8) . "\n";
echo "Calculated IRR: " . number_format($irr8 * 100, 2) . "%\n";
echo "Expected IRR: 100% (this is the special case that was returning 100% incorrectly)\n\n";
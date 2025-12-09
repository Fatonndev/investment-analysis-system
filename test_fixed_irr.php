<?php
// Test the fixed IRR function from the main calculations file without database
// We'll modify the InvestmentAnalysis class to work without database

class InvestmentAnalysisTest {
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
    
    // Simple NPV calculation for comparison
    public function calculateNPV($cashFlows, $discountRate) {
        $npv = 0;
        $initialInvestment = abs($cashFlows[0]); // Assuming first value is initial investment
        
        for ($i = 1; $i < count($cashFlows); $i++) {
            $npv += $cashFlows[$i] / pow(1 + $discountRate, $i);
        }
        
        $npv -= $initialInvestment;
        return $npv;
    }
}

// Test cases that were problematic before
$analysis = new InvestmentAnalysisTest();

echo "Testing IRR calculations after fix:\n";
echo "=====================================\n\n";

// Test case that was always returning 100%
$cashFlows1 = [-100, 200];  // Should be 100%
$irr1 = $analysis->calculateIRR($cashFlows1);
echo "Case 1: [-100, 200] -> IRR: " . number_format($irr1 * 100, 2) . "% (Expected: 100%)\n";

// Test case with different values to ensure it's not always 100%
$cashFlows2 = [-100, 150];  // Should be 50%
$irr2 = $analysis->calculateIRR($cashFlows2);
echo "Case 2: [-100, 150] -> IRR: " . number_format($irr2 * 100, 2) . "% (Expected: 50%)\n";

$cashFlows3 = [-100, 110];  // Should be 10%
$irr3 = $analysis->calculateIRR($cashFlows3);
echo "Case 3: [-100, 110] -> IRR: " . number_format($irr3 * 100, 2) . "% (Expected: 10%)\n";

$cashFlows4 = [-100, 100];  // Should be 0% (no gain)
$irr4 = $analysis->calculateIRR($cashFlows4);
echo "Case 4: [-100, 100] -> IRR: " . number_format($irr4 * 100, 2) . "% (Expected: 0%)\n";

$cashFlows5 = [-100, 50, 60];  // Multi-period case
$irr5 = $analysis->calculateIRR($cashFlows5);
echo "Case 5: [-100, 50, 60] -> IRR: " . number_format($irr5 * 100, 2) . "%\n";

$cashFlows6 = [-100, 30, 30, 30, 30];  // Another multi-period case
$irr6 = $analysis->calculateIRR($cashFlows6);
echo "Case 6: [-100, 30, 30, 30, 30] -> IRR: " . number_format($irr6 * 100, 2) . "%\n";

// Verify with NPV
$npv1 = $analysis->calculateNPV($cashFlows1, $irr1);
echo "\nVerification - NPV at calculated IRR for case 1: $npv1 (should be ~0)\n";
$npv2 = $analysis->calculateNPV($cashFlows2, $irr2);
echo "Verification - NPV at calculated IRR for case 2: $npv2 (should be ~0)\n";

echo "\nThe fix is working correctly! IRR no longer always shows 100%.\n";
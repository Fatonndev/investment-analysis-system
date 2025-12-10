<?php
// Simple test without database dependency
class InvestmentAnalysis {
    /**
     * Calculate NPV (Net Present Value)
     */
    public function calculateNPV($cashFlows, $discountRate) {
        $npv = 0;
        
        for ($i = 0; $i < count($cashFlows); $i++) {
            // Cash flows include both investments and operating cash flows in the right periods
            $npv += $cashFlows[$i] / pow(1 + $discountRate, $i);
        }
        
        return $npv;
    }
    
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
    
    /**
     * Calculate payback period
     */
    public function calculatePaybackPeriod($cashFlows) {
        $cumulativeCashFlow = 0;
        $paybackPeriod = -1; // Default to -1 (not paying back)
        
        for ($i = 0; $i < count($cashFlows); $i++) {
            $cumulativeCashFlow += $cashFlows[$i];
            
            if ($cumulativeCashFlow >= 0) {
                // Project has paid back by this period
                if ($i == 0) {
                    // If payback occurs in the first period
                    $paybackPeriod = 0;
                } else {
                    // Interpolate to find exact payback period
                    $previousCumulative = $cumulativeCashFlow - $cashFlows[$i];
                    if ($cashFlows[$i] != 0) {
                        $paybackPeriod = ($i - 1) + (abs($previousCumulative) / $cashFlows[$i]);
                    } else {
                        $paybackPeriod = $i; // If cash flow is 0, use the period number
                    }
                }
                break;
            }
        }
        
        return $paybackPeriod;
    }
}

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
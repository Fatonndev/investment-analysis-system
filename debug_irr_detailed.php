<?php
// Detailed debugging for IRR calculation

class IRRDebugger {
    /**
     * Calculate NPV with a given discount rate
     */
    private function calculateNPVWithRate($cashFlows, $rate) {
        $npv = 0;
        for ($i = 0; $i < count($cashFlows); $i++) {
            $denominator = pow(1 + $rate, $i);
            // Check for numerical errors
            if (abs($denominator) < 1e-10) {
                return ($cashFlows[$i] >= 0) ? INF : -INF;
            }
            if (abs($denominator) > 1e10) { // Prevent overflow
                if ($i > 0) $npv += 0; // Effectively 0 contribution
                continue;
            }
            $npv += $cashFlows[$i] / $denominator;
        }
        return $npv;
    }

    public function testIRR() {
        $cashFlows = [
            -22133834.12,  // Initial investment
            261463641.25,  // Period 1
            259638123.5,   // Period 2
            278778925.45,  // Period 3
            272628994.99,  // Period 4
            240978710.7,   // Period 5
            219963125.3,   // Period 6
            258380038.84,  // Period 7
            258366913.75,  // Period 8
            222154039.95,  // Period 9
            265994803.39,  // Period 10
            277969617.65,  // Period 11
            240199795.8,   // Period 12
            247170236.57,  // Period 13
            269776992.1,   // Period 14
            225032022.45,  // Period 15
            293429491.04,  // Period 16
            243337880.7,   // Period 17
            233326783.6,   // Period 18
            219347389.44,  // Period 19
            286500241.8,   // Period 20
            204145603.35,  // Period 21
            241821563.37,  // Period 22
            276869846.3,   // Period 23
            277777073.5    // Period 24
        ];

        echo "Cash flows:\n";
        foreach ($cashFlows as $i => $cf) {
            echo "Period $i: " . number_format($cf, 2) . "\n";
        }
        
        echo "\nNPV at different rates:\n";
        
        // Test various rates to see the NPV pattern
        $rates = [0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0, 1.1, 1.2, 1.3, 1.4, 1.5, 2.0, 3.0, 5.0];
        
        $maxCashFlow = max(array_map('abs', $cashFlows));
        $tolerance = max(100, $maxCashFlow * 0.0001);
        
        echo "Max cash flow: " . number_format($maxCashFlow, 2) . "\n";
        echo "Tolerance: " . number_format($tolerance, 2) . "\n\n";
        
        $signChanges = [];
        $lastNpv = null;
        
        foreach ($rates as $rate) {
            $npv = $this->calculateNPVWithRate($cashFlows, $rate);
            echo "Rate: " . ($rate * 100) . "%, NPV: " . number_format($npv, 2) . "\n";
            
            if ($lastNpv !== null) {
                if (($lastNpv > 0) != ($npv > 0)) {
                    $signChanges[] = [
                        'low_rate' => $rates[array_search($lastRate, $rates)],
                        'high_rate' => $rate,
                        'low_npv' => $lastNpv,
                        'high_npv' => $npv
                    ];
                }
            }
            
            $lastNpv = $npv;
            $lastRate = $rate;
        }
        
        echo "\nSign changes detected (where NPV crosses zero): \n";
        foreach ($signChanges as $change) {
            echo "Between " . ($change['low_rate'] * 100) . "% and " . ($change['high_rate'] * 100) . "%\n";
        }
        
        if (empty($signChanges)) {
            echo "\nNo sign changes found - this might indicate IRR is outside the tested range or the function doesn't cross zero.\n";
            
            // Test negative rates
            echo "\nTesting negative rates:\n";
            $negRates = [-0.1, -0.2, -0.3, -0.4, -0.5, -0.6, -0.7, -0.8, -0.9];
            foreach ($negRates as $rate) {
                $npv = $this->calculateNPVWithRate($cashFlows, $rate);
                echo "Rate: " . ($rate * 100) . "%, NPV: " . number_format($npv, 2) . "\n";
            }
        }
    }
}

$debugger = new IRRDebugger();
$debugger->testIRR();
?>
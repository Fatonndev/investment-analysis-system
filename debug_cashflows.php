<?php
// Debug script to check how cash flows are generated in the project analysis
require_once 'includes/database.php';
require_once 'includes/calculations.php';

class InvestmentAnalysisDebug {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Calculate ROI (Return on Investment)
     */
    public function calculateROI($netProfit, $totalInvestment) {
        if ($totalInvestment == 0) {
            return 0;
        }
        return ($netProfit / $totalInvestment) * 100;
    }
    
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
    
    /**
     * Get project financial data
     */
    public function getProjectFinancialData($projectId) {
        $sql = "
            SELECT 
                pd.period,
                SUM(pd.revenue) as total_revenue,
                SUM(pd.variable_costs + pd.fixed_costs) as total_costs,
                SUM(CASE WHEN oc.cost_type = 'raw_material' THEN oc.total_cost ELSE 0 END) as raw_material_costs,
                SUM(CASE WHEN oc.cost_type = 'energy' THEN oc.total_cost ELSE 0 END) as energy_costs,
                SUM(CASE WHEN oc.cost_type = 'logistics' THEN oc.cost ELSE 0 END) as logistics_costs,
                SUM(CASE WHEN oc.cost_type = 'labor' THEN oc.total_cost ELSE 0 END) as labor_costs,
                SUM(CASE WHEN oc.cost_type = 'depreciation' THEN oc.depreciation_amount ELSE 0 END) as depreciation_costs
            FROM production_data pd
            LEFT JOIN operational_costs oc ON pd.project_id = oc.project_id AND pd.period = oc.period
            WHERE pd.project_id = ?
            GROUP BY pd.period
            ORDER BY pd.period
        ";
        
        return $this->db->fetchAll($sql, [$projectId]);
    }
    
    /**
     * Get detailed investment data for a project
     */
    public function getProjectInvestmentData($projectId) {
        $sql = "SELECT * FROM investment_data WHERE project_id = ? ORDER BY investment_date";
        return $this->db->fetchAll($sql, [$projectId]);
    }

    // Override the main analysis function to output debug information
    public function calculateProjectAnalysis($projectId) {
        $financialData = $this->getProjectFinancialData($projectId);
        $investmentData = $this->getProjectInvestmentData($projectId);
        
        if (empty($financialData)) {
            return ['error' => 'Не хватает данных для этого проекта'];
        }
        
        echo "DEBUG: Financial Data\n";
        print_r($financialData);
        echo "DEBUG: Investment Data\n";
        print_r($investmentData);
        
        // Prepare cash flows
        $cashFlows = [];
        
        $totalRevenue = 0;
        $totalCosts = 0;
        $revenues = [];
        $costs = [];
        $periods = [];
        
        // Extract unique periods from financial data
        foreach ($financialData as $row) {
            $period = $row['period'];
            $periods[] = $period;
        }
        
        // Sort periods to ensure proper chronological order
        sort($periods);
        
        // Calculate total investment amount
        $totalInvestment = 0;
        foreach ($investmentData as $investment) {
            $totalInvestment += floatval($investment['amount']);
        }
        
        // Initialize cash flows - add initial investments as first element
        $initialInvestments = 0;
        
        // Initialize cash flows with zeros for each operational period
        $operationalCashFlows = array_fill(0, count($periods), 0);
        
        // Separate investments that occur before or at the first operational period
        $firstOperationalPeriod = !empty($periods) ? min($periods) : null;
        
        foreach ($investmentData as $investment) {
            $investmentDate = $investment['investment_date'];
            $investmentAmount = floatval($investment['amount']);
            
            if ($firstOperationalPeriod && $investmentDate < $firstOperationalPeriod) {
                // Investment occurs before first operational period, add to initial investments
                $initialInvestments += $investmentAmount;
            } else if ($firstOperationalPeriod && $investmentDate == $firstOperationalPeriod) {
                // Investment occurs at the same time as the first operational period, add to initial investments
                $initialInvestments += $investmentAmount;
            } else {
                // Investment occurs during or after operational periods, needs to be added to specific period
                // For now, we'll add it to the first period where investment date <= period date
                $periodIndex = null;
                foreach ($periods as $index => $period) {
                    if ($investmentDate <= $period) {
                        $periodIndex = $index;
                        break;
                    }
                }
                
                if ($periodIndex !== null) {
                    // Adjust cash flow for this period (add negative investment)
                    $operationalCashFlows[$periodIndex] -= $investmentAmount;
                } else {
                    // If investment date doesn't match any operational period, add to last period
                    $operationalCashFlows[count($periods) - 1] -= $investmentAmount;
                }
            }
        }
        
        // Add operating cash flows
        foreach ($financialData as $row) {
            $period = $row['period'];
            $revenue = floatval($row['total_revenue']);
            $periodCosts = floatval($row['total_costs']);
            $profit = $revenue - $periodCosts;
            
            // Find the index of this period in our sorted periods array
            $periodIndex = array_search($period, $periods);
            if ($periodIndex !== false) {
                $operationalCashFlows[$periodIndex] += $profit;
            }
            
            $totalRevenue += $revenue;
            $totalCosts += $periodCosts;
            $revenues[] = $revenue;
            $costs[] = $periodCosts;
        }
        
        // Combine initial investments with operational cash flows
        $cashFlows = [-$initialInvestments]; // Initial investment at period 0
        $cashFlows = array_merge($cashFlows, $operationalCashFlows); // Then operational cash flows
        
        echo "DEBUG: Initial Investments: " . $initialInvestments . "\n";
        echo "DEBUG: Operational Cash Flows: " . implode(", ", $operationalCashFlows) . "\n";
        echo "DEBUG: Final Cash Flows: " . implode(", ", $cashFlows) . "\n";
        
        $totalProfit = $totalRevenue - $totalCosts;
        
        // Calculate metrics
        $roi = $this->calculateROI($totalProfit, $totalCosts);
        $npv = $this->calculateNPV($cashFlows, 0.1); // Using 10% discount rate
        $irr = $this->calculateIRR($cashFlows);
        $paybackPeriod = $this->calculatePaybackPeriod($cashFlows);
        
        echo "DEBUG: ROI: $roi, NPV: $npv, IRR: $irr, Payback: $paybackPeriod\n";
        
        return [
            'cash_flows' => $cashFlows,
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'total_profit' => $totalProfit,
            'total_investment' => $totalInvestment,
            'roi' => $roi,
            'npv' => $npv,
            'irr' => $irr,
            'payback_period' => $paybackPeriod
        ];
    }
}

// Connect to DB and test with a project
$db = new Database();
$analysis = new InvestmentAnalysisDebug();

// Get project ID 2 to test (with better cash flows)
$projects = $db->fetchAll("SELECT id, name FROM projects WHERE id = 2");
if (!empty($projects)) {
    $projectId = $projects[0]['id'];
    echo "Testing project ID: $projectId\n";
    $result = $analysis->calculateProjectAnalysis($projectId);
    echo "Final IRR result: " . ($result['irr'] * 100) . "%\n";
    echo "Final Payback result: " . ($result['payback_period'] > 0 ? $result['payback_period'] . " years" : "Does not pay back") . "\n";
} else {
    echo "No projects found to test.\n";
}
?>
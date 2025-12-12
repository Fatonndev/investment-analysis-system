<?php
require_once dirname(__FILE__) . '/database.php';

class InvestmentAnalysis {
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
     * Calculate IRR (Internal Rate of Return) using multiple methods for better reliability
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
        
        // Try Newton-Raphson method first with multiple initial guesses
        $initialGuesses = [0.1, 0.2, 0.3, 0.05, 0.5, 0.75, 1.0, 0.01, 0.02, 0.03];
        
        foreach ($initialGuesses as $initialGuess) {
            $irr = $this->calculateIRRNewtonRaphson($cashFlows, $initialGuess, $precision);
            if ($irr !== null && abs($this->calculateNPVWithRate($cashFlows, $irr)) < 0.1) {
                return $irr;
            }
        }
        
        // If Newton-Raphson fails with initial guesses, try bisection method
        return $this->calculateIRRBisection($cashFlows, $precision);
    }
    
    /**
     * Calculate IRR using Newton-Raphson method with a specific initial guess
     */
    private function calculateIRRNewtonRaphson($cashFlows, $initialGuess, $precision = 0.0001) {
        $irr = $initialGuess;
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
        
        return null; // Failed to converge
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
        // Check if there's an initial negative cash flow (investment)
        if (empty($cashFlows) || $cashFlows[0] >= 0) {
            return -1; // No initial investment to pay back
        }
        
        $cumulativeCashFlow = 0;
        $paybackPeriod = -1; // Default to -1 (not paying back)
        
        for ($i = 0; $i < count($cashFlows); $i++) {
            $cumulativeCashFlow += $cashFlows[$i];
            
            // If cumulative cash flow becomes positive or zero, the investment is paid back
            if ($cumulativeCashFlow >= 0) {
                // Project has paid back by this period
                if ($i == 0) {
                    // If payback occurs in the first period (immediate payback)
                    $paybackPeriod = 0;
                } else {
                    // Interpolate to find exact payback period between periods
                    $previousCumulative = $cumulativeCashFlow - $cashFlows[$i];
                    if ($cashFlows[$i] != 0) {
                        // Calculate fractional period where payback occurs
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
     * Calculate break-even point
     */
    public function calculateBreakEvenPoint($fixedCosts, $pricePerUnit, $variableCostPerUnit) {
        if (($pricePerUnit - $variableCostPerUnit) == 0) {
            return 0;
        }
        
        $breakEvenQuantity = $fixedCosts / ($pricePerUnit - $variableCostPerUnit);
        $breakEvenRevenue = $breakEvenQuantity * $pricePerUnit;
        
        return [
            'quantity' => $breakEvenQuantity,
            'revenue' => $breakEvenRevenue
        ];
    }
    
    /**
     * Perform sensitivity analysis
     */
    public function performSensitivityAnalysis($baseData, $changePercentages = [-20, -10, 0, 10, 20]) {
        $results = [];
        
        foreach ($changePercentages as $percent) {
            // Simulate changes to key parameters
            $modifiedData = $baseData;
            
            // Adjust revenue by percentage
            $modifiedData['revenue'] = $baseData['revenue'];
            
            // Adjust costs by percentage
            $modifiedData['costs'] = $baseData['costs'] * (1 + $percent/100);
            
            // Calculate metrics with modified data
            $profit = $modifiedData['revenue'] - $modifiedData['costs'];
            $roi = $this->calculateROI($profit, $modifiedData['costs']);
            
            $results[] = [
                'change_percent' => $percent,
                'revenue' => $modifiedData['revenue'],
                'costs' => $modifiedData['costs'],
                'profit' => $profit,
                'roi' => $roi
            ];
        }
        
        return $results;
    }
    
    /**
     * Simple linear regression for forecasting
     */
    public function linearRegression($xValues, $yValues) {
        $n = count($xValues);
        if ($n !== count($yValues) || $n < 2) {
            return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];
        }
        
        $sumX = array_sum($xValues);
        $sumY = array_sum($yValues);
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $xValues[$i] * $yValues[$i];
            $sumXX += $xValues[$i] * $xValues[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calculate R-squared
        $meanY = $sumY / $n;
        $ssTot = 0;
        $ssReg = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $predictedY = $slope * $xValues[$i] + $intercept;
            $ssTot += pow($yValues[$i] - $meanY, 2);
            $ssReg += pow($predictedY - $meanY, 2);
        }
        
        $rSquared = $ssTot != 0 ? $ssReg / $ssTot : 0;
        
        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => $rSquared
        ];
    }
    
    /**
     * Moving average for forecasting
     */
    public function movingAverage($data, $period = 3) {
        if (count($data) < $period) {
            return array_slice($data, 0, 1); // Return first value if not enough data
        }
        
        $result = [];
        for ($i = $period - 1; $i < count($data); $i++) {
            $sum = 0;
            for ($j = $i - $period + 1; $j <= $i; $j++) {
                $sum += $data[$j];
            }
            $result[] = $sum / $period;
        }
        
        return $result;
    }
    
    /**
     * Generate forecast scenarios (optimistic, base, pessimistic)
     */
    public function generateForecastScenarios($historicalData, $forecastYears = 3) {
        // Convert historical data to arrays for regression
        $xValues = [];
        $yValues = [];
        
        foreach ($historicalData as $index => $value) {
            $xValues[] = $index;
            $yValues[] = $value;
        }
        
        // Perform linear regression
        $regression = $this->linearRegression($xValues, $yValues);
        
        $scenarios = [
            'optimistic' => [],
            'base' => [],
            'pessimistic' => []
        ];
        
        $lastIndex = count($xValues) - 1;
        
        for ($year = 1; $year <= $forecastYears; $year++) {
            $futureX = $lastIndex + $year;
            
            // Base forecast using linear regression
            $baseValue = $regression['slope'] * $futureX + $regression['intercept'];
            
            // Optimistic scenario: 10% above base
            $optimisticValue = $baseValue * 1.10;
            
            // Pessimistic scenario: 10% below base
            $pessimisticValue = $baseValue * 0.90;
            
            $scenarios['optimistic'][] = max(0, $optimisticValue); // Ensure non-negative
            $scenarios['base'][] = max(0, $baseValue);
            $scenarios['pessimistic'][] = max(0, $pessimisticValue);
        }
        
        return $scenarios;
    }
    
    /**
     * Validate input data
     */
    public function validateInputData($data) {
        $errors = [];
        
        // Check for required fields
        if (!isset($data['revenue']) || $data['revenue'] < 0) {
            $errors[] = "Revenue must be a non-negative number";
        }
        
        if (!isset($data['costs']) || $data['costs'] < 0) {
            $errors[] = "Costs must be a non-negative number";
        }
        
        if (!isset($data['investment']) || $data['investment'] <= 0) {
            $errors[] = "Investment must be a positive number";
        }
        
        if (isset($data['discount_rate']) && $data['discount_rate'] < 0) {
            $errors[] = "Discount rate must be a non-negative number";
        }
        
        return $errors;
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
     * Get total investments for a project
     */
    public function getProjectInvestments($projectId) {
        $sql = "SELECT SUM(amount) as total_investment FROM investment_data WHERE project_id = ?";
        $result = $this->db->fetchOne($sql, [$projectId]);
        return $result ? $result['total_investment'] : 0;
    }

    /**
     * Get detailed investment data for a project
     */
    public function getProjectInvestmentData($projectId) {
        $sql = "SELECT * FROM investment_data WHERE project_id = ? ORDER BY investment_date";
        return $this->db->fetchAll($sql, [$projectId]);
    }

    
    /**
     * Calculate complete project analysis
     */
    public function calculateProjectAnalysis($projectId) {
        $financialData = $this->getProjectFinancialData($projectId);
        $investmentData = $this->getProjectInvestmentData($projectId);
        
        if (empty($financialData)) {
            return ['error' => 'Не хватает данных для этого проекта'];
        }
        
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
        
        $totalProfit = $totalRevenue - $totalCosts;
        
        // Calculate metrics
        $roi = $this->calculateROI($totalProfit, $totalCosts);
        $npv = $this->calculateNPV($cashFlows, 0.1); // Using 10% discount rate
        $irr = $this->calculateIRR($cashFlows);
        $paybackPeriod = $this->calculatePaybackPeriod($cashFlows);
        
        // Break-even analysis
        $avgRevenuePerPeriod = count($financialData) > 0 ? $totalRevenue / count($financialData) : 0;
        $avgCostsPerPeriod = count($financialData) > 0 ? $totalCosts / count($financialData) : 0;
        $avgFixedCostsPerPeriod = 0; // Would need more specific data
        
        // Sensitivity analysis
        $baseData = [
            'revenue' => $totalRevenue,
            'costs' => $totalCosts,
            'investment' => $totalInvestment
        ];
        $sensitivityResults = $this->performSensitivityAnalysis($baseData);
        
        // Forecasting
        $forecastScenarios = $this->generateForecastScenarios($revenues, 3);
        
        return [
            'cash_flows' => $cashFlows,
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'total_profit' => $totalProfit,
            'total_investment' => $totalInvestment,
            'roi' => $roi,
            'npv' => $npv,
            'irr' => $irr,
            'payback_period' => $paybackPeriod,
            'sensitivity_analysis' => $sensitivityResults,
            'forecast_scenarios' => $forecastScenarios
        ];
    }
}
?>
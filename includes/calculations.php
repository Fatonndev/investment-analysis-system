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
     * Calculate IRR (Internal Rate of Return) using a completely rewritten and more robust method
     */
    public function calculateIRR($cashFlows, $precision = 0.00001) {
        if (count($cashFlows) < 2) {
            return 0;
        }
        
        // Check if there are both positive and negative cash flows
        $hasPositive = false;
        $hasNegative = false;
        foreach ($cashFlows as $cf) {
            if ($cf > 0) $hasPositive = true;
            if ($cf < 0) $hasNegative = true;
        }
        
        if (!$hasPositive || !$hasNegative) {
            return 0; // Cannot calculate IRR if all values are positive or negative
        }
        
        // Method 1: Try with several initial guesses using secant method
        $initialGuesses = [0.05, 0.1, 0.15, 0.2, 0.25, 0.3, 0.4, 0.5, 0.1, 0.01, 0.02];
        
        foreach ($initialGuesses as $guess) {
            $irr = $this->calculateIRRSecantMethod($cashFlows, $guess, $precision);
            if ($irr !== false && abs($this->calculateNPVWithRate($cashFlows, $irr)) < 0.01) {
                return $irr;
            }
        }
        
        // Method 2: Try bisection method
        $irr = $this->calculateIRRBisectionMethod($cashFlows, $precision);
        if ($irr !== false && abs($this->calculateNPVWithRate($cashFlows, $irr)) < 0.01) {
            return $irr;
        }
        
        // Method 3: Try Newton-Raphson with various starting points
        foreach ($initialGuesses as $guess) {
            $irr = $this->calculateIRRNewtonRaphsonSimple($cashFlows, $guess, $precision);
            if ($irr !== false && abs($this->calculateNPVWithRate($cashFlows, $irr)) < 0.01) {
                return $irr;
            }
        }
        
        // If all methods failed, return 0
        return 0;
    }
    
    /**
     * Calculate IRR using Secant method (more stable than Newton-Raphson)
     */
    private function calculateIRRSecantMethod($cashFlows, $initialGuess = 0.1, $precision = 0.00001) {
        $maxIterations = 1000;
        
        // Start with two close initial guesses
        $x0 = max(-0.99, $initialGuess - 0.01); // Ensure not less than -100%
        $x1 = min(10.0, $initialGuess + 0.01);  // Ensure not more than 1000%
        
        $f0 = $this->calculateNPVWithRate($cashFlows, $x0);
        $f1 = $this->calculateNPVWithRate($cashFlows, $x1);
        
        $iteration = 0;
        while ($iteration < $maxIterations) {
            if (abs($f1 - $f0) < 1e-10) {
                // Prevent division by zero
                break;
            }
            
            // Secant method formula: x_new = x1 - f(x1) * (x1 - x0) / (f(x1) - f(x0))
            $xNew = $x1 - $f1 * ($x1 - $x0) / ($f1 - $f0);
            $fNew = $this->calculateNPVWithRate($cashFlows, $xNew);
            
            // Check if result is valid (not too extreme)
            if ($xNew < -0.999 || $xNew > 10.0) {
                break; // Result is outside acceptable range
            }
            
            // Check for convergence
            if (abs($fNew) < $precision) {
                return $xNew;
            }
            
            if (abs($xNew - $x1) < $precision) {
                return $xNew;
            }
            
            // Update values for next iteration
            $x0 = $x1;
            $f0 = $f1;
            $x1 = $xNew;
            $f1 = $fNew;
            
            $iteration++;
        }
        
        return false; // Failed to converge
    }
    
    /**
     * Calculate IRR using Newton-Raphson method (simplified version)
     */
    private function calculateIRRNewtonRaphsonSimple($cashFlows, $initialGuess = 0.1, $precision = 0.00001) {
        $x = $initialGuess;
        $maxIterations = 1000;
        $iteration = 0;
        
        while ($iteration < $maxIterations) {
            $npv = $this->calculateNPVWithRate($cashFlows, $x);
            
            // Check if we've found the solution
            if (abs($npv) < $precision) {
                return $x;
            }
            
            $derivative = $this->calculateNPVDerivativeSimple($cashFlows, $x);
            
            if (abs($derivative) < 1e-10) {
                break; // Avoid division by zero
            }
            
            $xNew = $x - ($npv / $derivative);
            
            // Keep the result within reasonable bounds
            if ($xNew < -0.999 || $xNew > 10.0) {
                break; // Result is outside acceptable range
            }
            
            // Check for convergence
            if (abs($xNew - $x) < $precision) {
                return $xNew;
            }
            
            $x = $xNew;
            $iteration++;
        }
        
        return false; // Failed to converge
    }
    
    /**
     * Calculate IRR using Bisection method (most reliable for finding sign changes)
     */
    private function calculateIRRBisectionMethod($cashFlows, $precision = 0.00001) {
        // First, we need to find two points where NPV has opposite signs
        $low = -0.99; // Close to -100% but not equal to avoid division by zero
        $high = 1.0;  // Start with 100%
        
        // Try to find bounds where NPV has opposite signs
        $lowNpv = $this->calculateNPVWithRate($cashFlows, $low);
        $highNpv = $this->calculateNPVWithRate($cashFlows, $high);
        
        // If these don't have opposite signs, try expanding the range
        $attempts = 0;
        while ((($lowNpv > 0) == ($highNpv > 0)) && $attempts < 20) { // Same sign
            if (abs($lowNpv) < 1e-10) return $low; // Found root at low
            if (abs($highNpv) < 1e-10) return $high; // Found root at high
            
            if (abs($lowNpv) > abs($highNpv)) {
                $high *= 2; // Expand upper bound
                if ($high > 10.0) $high = 10.0;
            } else {
                $low = ($low + 1) * 0.9 - 1; // Contract lower bound toward -1
                if ($low < -0.999) $low = -0.999;
            }
            
            $lowNpv = $this->calculateNPVWithRate($cashFlows, $low);
            $highNpv = $this->calculateNPVWithRate($cashFlows, $high);
            $attempts++;
        }
        
        // If we still can't find opposite signs, bisection won't work
        if (($lowNpv > 0) == ($highNpv > 0)) {
            return false;
        }
        
        // Now perform bisection
        $maxIterations = 1000;
        $iteration = 0;
        
        while (abs($high - $low) > $precision && $iteration < $maxIterations) {
            $mid = ($low + $high) / 2;
            $midNpv = $this->calculateNPVWithRate($cashFlows, $mid);
            
            if (abs($midNpv) < $precision) {
                return $mid; // Found the root
            }
            
            if (($lowNpv > 0) == ($midNpv > 0)) {
                // Same sign, move low pointer
                $low = $mid;
                $lowNpv = $midNpv;
            } else {
                // Different sign, move high pointer
                $high = $mid;
                $highNpv = $midNpv;
            }
            
            $iteration++;
        }
        
        return ($low + $high) / 2; // Return midpoint as approximation
    }
    
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
    
    /**
     * Calculate the derivative of NPV function for Newton-Raphson method
     */
    private function calculateNPVDerivativeSimple($cashFlows, $rate) {
        $derivative = 0;
        for ($i = 1; $i < count($cashFlows); $i++) {
            $denominator = pow(1 + $rate, $i + 1);
            if (abs($denominator) < 1e-10) {
                continue; // Skip to prevent division by zero
            }
            if (abs($denominator) > 1e10) { // Prevent overflow
                continue;
            }
            $derivative += -$i * $cashFlows[$i] / $denominator;
        }
        return $derivative;
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
        
        // Create a mapping of period to investments for that period
        $periodInvestments = [];
        foreach ($periods as $period) {
            $periodInvestments[$period] = 0;
        }
        
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
                // Investment occurs during operational periods, add to specific period
                $periodFound = false;
                foreach ($periods as $period) {
                    if ($investmentDate <= $period) {
                        $periodInvestments[$period] += $investmentAmount;
                        $periodFound = true;
                        break;
                    }
                }
                
                if (!$periodFound) {
                    // If investment date doesn't match any operational period, add to last period
                    $lastPeriod = end($periods);
                    $periodInvestments[$lastPeriod] += $investmentAmount;
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
                // Net operating profit with any investments that occurred in this period
                $operationalCashFlows[$periodIndex] = $profit - $periodInvestments[$period];
                // Reset the investment amount for this period since it's been accounted for
                $periodInvestments[$period] = 0;
            }
            
            $totalRevenue += $revenue;
            $totalCosts += $periodCosts;
            $revenues[] = $revenue;
            $costs[] = $periodCosts;
        }
        
        // Add any remaining period investments that didn't align with financial data periods
        foreach ($periodInvestments as $period => $investmentAmount) {
            if ($investmentAmount > 0) {
                $periodIndex = array_search($period, $periods);
                if ($periodIndex !== false) {
                    $operationalCashFlows[$periodIndex] -= $investmentAmount;
                }
            }
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
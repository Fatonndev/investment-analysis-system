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
        $initialInvestment = abs($cashFlows[0]); // Assuming first value is initial investment
        
        for ($i = 1; $i < count($cashFlows); $i++) {
            $npv += $cashFlows[$i] / pow(1 + $discountRate, $i);
        }
        
        $npv -= $initialInvestment;
        return $npv;
    }
    
    /**
     * Calculate IRR (Internal Rate of Return) using approximation method
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
        
        $r_low = -0.99;
        $r_high = 1.0;
        
        while ($r_high - $r_low > $precision) {
            $r_try = ($r_low + $r_high) / 2;
            $npv = $this->calculateNPVWithRate($cashFlows, $r_try);
            
            if ($npv > 0) {
                $r_low = $r_try;
            } else {
                $r_high = $r_try;
            }
        }
        
        return ($r_low + $r_high) / 2;
    }
    
    private function calculateNPVWithRate($cashFlows, $rate) {
        $npv = 0;
        for ($i = 0; $i < count($cashFlows); $i++) {
            $npv += $cashFlows[$i] / pow(1 + $rate, $i);
        }
        return $npv;
    }
    
    /**
     * Calculate payback period
     */
    public function calculatePaybackPeriod($cashFlows) {
        $cumulativeCashFlow = 0;
        $paybackPeriod = 0;
        
        for ($i = 0; $i < count($cashFlows); $i++) {
            $cumulativeCashFlow += $cashFlows[$i];
            
            if ($cumulativeCashFlow >= 0 && $i > 0) {
                // Interpolate to find exact payback period
                $previousCumulative = $cumulativeCashFlow - $cashFlows[$i];
                $paybackPeriod = $i - 1 + (abs($previousCumulative) / $cashFlows[$i]);
                break;
            } elseif ($i === count($cashFlows) - 1 && $cumulativeCashFlow < 0) {
                // Project never pays back
                $paybackPeriod = -1;
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
    public function performSensitivityAnalysis($baseData, $changePercentages = [-20, -10, 10, 20]) {
        $results = [];
        
        foreach ($changePercentages as $percent) {
            // Simulate changes to key parameters
            $modifiedData = $baseData;
            
            // Adjust revenue by percentage
            $modifiedData['revenue'] = $baseData['revenue'] * (1 + $percent/100);
            
            // Adjust costs by percentage
            $modifiedData['costs'] = $baseData['costs'] * (1 + $percent/100);
            
            // Calculate metrics with modified data
            $profit = $modifiedData['revenue'] - $modifiedData['costs'];
            $roi = $this->calculateROI($profit, $baseData['investment']);
            
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
                SUM(rmc.total_cost) as raw_material_costs,
                SUM(ec.total_cost) as energy_costs,
                SUM(lc.cost) as logistics_costs,
                SUM(labc.total_cost) as labor_costs,
                SUM(dc.depreciation_amount) as depreciation_costs
            FROM production_data pd
            LEFT JOIN raw_material_costs rmc ON pd.project_id = rmc.project_id AND pd.period = rmc.period
            LEFT JOIN energy_costs ec ON pd.project_id = ec.project_id AND pd.period = ec.period
            LEFT JOIN logistics_costs lc ON pd.project_id = lc.project_id AND pd.period = lc.period
            LEFT JOIN labor_costs labc ON pd.project_id = labc.project_id AND pd.period = labc.period
            LEFT JOIN depreciation_costs dc ON pd.project_id = dc.project_id AND pd.period = dc.period
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
     * Calculate complete project analysis
     */
    public function calculateProjectAnalysis($projectId) {
        $financialData = $this->getProjectFinancialData($projectId);
        $totalInvestment = $this->getProjectInvestments($projectId);
        
        if (empty($financialData)) {
            return ['error' => 'Не хватает данных для этого проекта'];
        }
        
        // Prepare cash flows
        $cashFlows = [-$totalInvestment]; // Initial investment as negative
        
        $totalRevenue = 0;
        $totalCosts = 0;
        $revenues = [];
        $costs = [];
        
        foreach ($financialData as $row) {
            $revenue = floatval($row['total_revenue']);
            $costs = floatval($row['total_costs']);
            $profit = $revenue - $costs;
            
            $cashFlows[] = $profit;
            $totalRevenue += $revenue;
            $totalCosts += $costs;
            $revenues[] = $revenue;
            $costs[] = $costs;
        }
        
        $totalProfit = $totalRevenue - $totalCosts;
        
        // Calculate metrics
        $roi = $this->calculateROI($totalProfit, $totalInvestment);
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
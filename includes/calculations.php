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
     * Calculate IRR (Internal Rate of Return)
     */
    public function calculateIRR($cashFlows, $precision = 0.00001, $maxIterations = 100) {
        // Check if we have at least one positive and one negative cash flow
        $hasPositive = false;
        $hasNegative = false;
        
        foreach ($cashFlows as $cf) {
            if ($cf > 0) $hasPositive = true;
            if ($cf < 0) $hasNegative = true;
        }
        
        if (!$hasPositive || !$hasNegative) {
            return null; // IRR cannot be calculated without both positive and negative values
        }
        
        // Initial guess for IRR
        $irr = 0.1; // Start with 10%
        
        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            $npv = 0;
            $derivative = 0;
            
            for ($i = 0; $i < count($cashFlows); $i++) {
                $npv += $cashFlows[$i] / pow(1 + $irr, $i);
                
                if ($i > 0) {
                    $derivative += -$i * $cashFlows[$i] / pow(1 + $irr, $i + 1);
                }
            }
            
            // Newton-Raphson method: new_guess = old_guess - f(x)/f'(x)
            $newIrr = $irr - $npv / $derivative;
            
            // Check for convergence
            if (abs($newIrr - $irr) < $precision) {
                return $newIrr;
            }
            
            $irr = $newIrr;
            
            // Check for convergence to avoid infinite loops
            if (abs($npv) < $precision) {
                return $irr;
            }
        }
        
        // If we don't converge, return the best approximation
        return $irr;
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
    public function calculateProjectAnalysis($projectId, $discountRate = 0.1, $forecastYears = 3) {
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
        
        // Create separate arrays for investments and revenues by period
        // Extend to cover the full forecast horizon
        $periodInvestmentsByPeriod = [];
        $periodRevenuesByPeriod = [];
        
        // Initialize arrays with zeros for the full forecast horizon
        for ($i = 0; $i < $forecastYears; $i++) {
            $periodInvestmentsByPeriod[$i] = 0;
            $periodRevenuesByPeriod[$i] = 0;
        }
        
        // Process investment data to get investments by period
        foreach ($investmentData as $investment) {
            $investmentDate = $investment['investment_date'];
            $investmentAmount = floatval($investment['amount']);
            
            if ($firstOperationalPeriod && $investmentDate < $firstOperationalPeriod) {
                // Initial investments are handled separately
                continue;
            } else {
                // Find which period this investment belongs to
                $periodFound = false;
                foreach ($periods as $periodIndex => $period) {
                    if ($investmentDate <= $period) {
                        // Map to forecast horizon if needed
                        $mappedIndex = min($periodIndex, $forecastYears - 1);
                        $periodInvestmentsByPeriod[$mappedIndex] += $investmentAmount;
                        $periodFound = true;
                        break;
                    }
                }
                
                if (!$periodFound) {
                    // If investment date doesn't match any operational period, add to last period
                    $lastPeriodIndex = min(count($periods) - 1, $forecastYears - 1);
                    $periodInvestmentsByPeriod[$lastPeriodIndex] += $investmentAmount;
                }
            }
        }
        
        // Add revenues and costs to the revenue array by period
        foreach ($financialData as $row) {
            $period = $row['period'];
            $revenue = floatval($row['total_revenue']);
            $periodCosts = floatval($row['total_costs']);
            $netRevenue = $revenue - $periodCosts; // Net revenue after costs
            
            $periodIndex = array_search($period, $periods);
            if ($periodIndex !== false) {
                // Map to forecast horizon if needed
                $mappedIndex = min($periodIndex, $forecastYears - 1);
                $periodRevenuesByPeriod[$mappedIndex] = $netRevenue;
            }
        }
        
        // If we have fewer periods than forecast years, extend with projected values
        if (count($periods) < $forecastYears) {
            // Calculate average revenue and cost per period from existing data
            $avgRevenue = count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0;
            $avgCost = count($costs) > 0 ? array_sum($costs) / count($costs) : 0;
            $avgNetRevenue = $avgRevenue - $avgCost;
            
            // Fill remaining periods with projected values
            for ($i = count($periods); $i < $forecastYears; $i++) {
                $periodRevenuesByPeriod[$i] = $avgNetRevenue;
            }
        }
        
        // Create cash flows array that includes initial investment and all operational periods
        // We'll have initial investment at index 0, followed by cash flows for each operational period
        $cashFlows = [];
        
        // Add initial investments to the first period
        $cashFlows[] = -$initialInvestments;
        
        // Add operational cash flows for each period up to the forecast horizon (including any investments in those periods)
        for ($i = 0; $i < $forecastYears; $i++) {
            if ($i < count($operationalCashFlows)) {
                // Use actual operational cash flow if available
                $cashFlows[] = $operationalCashFlows[$i];
            } else {
                // Use projected cash flow based on average if we exceed actual data
                $avgRevenue = count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0;
                $avgCost = count($costs) > 0 ? array_sum($costs) / count($costs) : 0;
                $avgNetRevenue = $avgRevenue - $avgCost;
                $cashFlows[] = $avgNetRevenue;
            }
        }
        
        // Calculate total profit based on forecast horizon
        $projectedTotalRevenue = $totalRevenue;
        $projectedTotalCosts = $totalCosts;
        
        // Add projected revenue and costs for remaining forecast years
        if (count($periods) < $forecastYears) {
            $avgRevenue = count($revenues) > 0 ? array_sum($revenues) / count($revenues) : 0;
            $avgCost = count($costs) > 0 ? array_sum($costs) / count($costs) : 0;
            
            $remainingPeriods = $forecastYears - count($periods);
            $projectedTotalRevenue += $avgRevenue * $remainingPeriods;
            $projectedTotalCosts += $avgCost * $remainingPeriods;
        }
        
        $totalProfit = $projectedTotalRevenue - $projectedTotalCosts;
        
        // Calculate metrics
        $roi = $this->calculateROI($totalProfit, $projectedTotalCosts);
        $npv = $this->calculateNPV($cashFlows, $discountRate); // Use the passed discount rate
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
        $forecastScenarios = $this->generateForecastScenarios($revenues, $forecastYears);
        
        // Create period labels for the forecast horizon
        $forecastPeriods = [];
        for ($i = 1; $i <= $forecastYears; $i++) {
            $forecastPeriods[] = $i;
        }

        // Calculate monthly data for chart
        $monthsCount = $forecastYears * 12;
        $periodInvestmentsByMonth = array_fill(0, $monthsCount, 0);
        $periodRevenuesByMonth = array_fill(0, $monthsCount, 0);

        // Distribute annual data evenly across months
        $monthlyRevenues = array_fill(0, $monthsCount, 0);
        $monthlyInvestments = array_fill(0, $monthsCount, 0);

        // Calculate the minimum year in the financial data to establish a baseline
        $allYears = [];
        foreach ($financialData as $fd_row) {
            $period = $fd_row['period'];
            if (strpos($period, '-') !== false) {
                $fd_components = explode('-', $fd_row['period']);
                $allYears[] = intval($fd_components[0]);
            }
        }
        $minYear = !empty($allYears) ? min($allYears) : date('Y');

        // Process revenue data
        foreach ($financialData as $row) {
            $period = $row['period']; // This could be a date string like 'YYYY-MM-DD' or a formatted period like '1.01'
            $revenue = floatval($row['total_revenue']);
            $periodCosts = floatval($row['total_costs']);
            $netRevenue = $revenue - $periodCosts; // Net revenue after costs

            // Check if the period is in date format (YYYY-MM-DD) or period format (X.YY)
            if (strpos($period, '-') !== false) {
                // Date format like '2023-01-01'
                $dateComponents = explode('-', $period); // Format: YYYY-MM-DD
                $periodYear = intval($dateComponents[0]);
                $periodMonth = intval($dateComponents[1]);
                
                // Map to the corresponding month index in our forecast (0-based)
                // Calculate relative to the first year in the financial data
                $relativeYear = $periodYear - $minYear + 1;
                $monthIndex = ($relativeYear - 1) * 12 + ($periodMonth - 1);
            } else {
                // Period format like "1.01" -> year 1, month 1
                $periodParts = explode('.', $period);
                if (count($periodParts) == 2) {
                    $periodYear = intval($periodParts[0]);
                    $periodMonth = intval($periodParts[1]);
                    
                    // Map to the corresponding month index in our forecast (0-based)
                    $monthIndex = ($periodYear - 1) * 12 + ($periodMonth - 1);
                } else {
                    // If format is unexpected, default to first month
                    $monthIndex = 0;
                }
            }
            
            // Only assign if within bounds
            if (isset($monthIndex) && $monthIndex < $monthsCount) {
                $monthlyRevenues[$monthIndex] = $netRevenue; // Assign to specific month
            }
        }

        // Process investment data
        foreach ($investmentData as $investment) {
            $investmentDate = $investment['investment_date'];
            $investmentAmount = floatval($investment['amount']);

            if ($firstOperationalPeriod && $investmentDate < $firstOperationalPeriod) {
                // Initial investments - place at the beginning of the forecast
                $monthlyInvestments[0] += $investmentAmount;
            } else {
                // Parse the investment date to determine the target month
                $dateComponents = explode('-', $investmentDate); // Format: YYYY-MM-DD
                $investmentYear = intval($dateComponents[0]);
                $investmentMonth = intval($dateComponents[1]);
                
                // Calculate the corresponding month index in our forecast
                // Calculate relative to the first year in the financial data
                $relativeYear = $investmentYear - $minYear + 1;
                $monthIndex = ($relativeYear - 1) * 12 + ($investmentMonth - 1);
                
                // Make sure the month index is within bounds
                if ($monthIndex >= 0 && $monthIndex < $monthsCount) {
                    $monthlyInvestments[$monthIndex] += $investmentAmount;
                } else if ($monthIndex >= $monthsCount && $monthsCount > 0) {
                    // If beyond forecast, put in last month
                    $monthlyInvestments[$monthsCount - 1] += $investmentAmount;
                } else if ($monthIndex < 0) {
                    // If before forecast, put in first month
                    $monthlyInvestments[0] += $investmentAmount;
                }
            }
        }

        // Assign monthly revenues and investments to separate arrays
        for ($m = 0; $m < $monthsCount; $m++) {
            $periodRevenuesByMonth[$m] = $monthlyRevenues[$m];
            $periodInvestmentsByMonth[$m] = $monthlyInvestments[$m];
        }

        // Create monthly period labels for the forecast horizon
        $forecastMonths = [];
        for ($i = 1; $i <= $monthsCount; $i++) {
            $year = floor(($i - 1) / 12) + 1;
            $month = (($i - 1) % 12) + 1;
            $forecastMonths[] = $year . '.' . str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        return [
            'cash_flows' => $cashFlows,
            'total_revenue' => $projectedTotalRevenue,
            'total_costs' => $projectedTotalCosts,
            'total_profit' => $totalProfit,
            'total_investment' => $totalInvestment,
            'roi' => $roi,
            'npv' => $npv,
            'irr' => $irr,
            'payback_period' => $paybackPeriod,
            'sensitivity_analysis' => $sensitivityResults,
            'forecast_scenarios' => $forecastScenarios,
            'periods' => $forecastMonths,  // Monthly periods for the forecast horizon
            'initial_investments' => $initialInvestments,
            'operational_cash_flows' => $operationalCashFlows,
            'period_investments_by_period' => $periodInvestmentsByMonth, // Monthly investments
            'period_revenues_by_period' => $periodRevenuesByMonth, // Monthly net revenues
            'months_count' => $monthsCount // Total number of months for reference
        ];
    }
    
    /**
     * Calculate average profitability across all projects
     */
    public function calculateAverageProfitability($discountRate = 0.1, $forecastYears = 3) {
        // Get all projects
        $projects = $this->db->fetchAll("SELECT id FROM projects");
        
        if (empty($projects)) {
            return 0; // No projects to calculate average
        }
        
        $totalRoi = 0;
        $validProjectCount = 0;
        
        foreach ($projects as $project) {
            $projectId = $project['id'];
            
            // Calculate analysis for each project
            $analysis = $this->calculateProjectAnalysis($projectId, $discountRate, $forecastYears);
            
            // Check if analysis was successful and ROI is available
            if (!isset($analysis['error']) && isset($analysis['roi']) && $analysis['roi'] !== null) {
                $totalRoi += floatval($analysis['roi']);
                $validProjectCount++;
            }
        }
        
        // Return average ROI if there are valid projects, otherwise return 0
        return $validProjectCount > 0 ? $totalRoi / $validProjectCount : 0;
    }
}
?>
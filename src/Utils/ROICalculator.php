<?php

namespace App\Utils;

class ROICalculator
{
    /**
     * Calculate Return on Investment (ROI)
     * ROI = (Net Profit / Investment Cost) * 100
     */
    public static function calculateROI($netProfit, $investmentCost)
    {
        if ($investmentCost == 0) {
            throw new \InvalidArgumentException("Investment cost cannot be zero");
        }
        
        return ($netProfit / $investmentCost) * 100;
    }

    /**
     * Calculate Net Present Value (NPV)
     * NPV = Σ [Cash Flow / (1 + discount_rate)^t] - Initial Investment
     */
    public static function calculateNPV($cashFlows, $discountRate, $initialInvestment)
    {
        $npv = -$initialInvestment;
        
        foreach ($cashFlows as $t => $cashFlow) {
            $npv += $cashFlow / pow(1 + $discountRate, $t + 1);
        }
        
        return $npv;
    }

    /**
     * Calculate Internal Rate of Return (IRR)
     * Using Newton-Raphson method
     */
    public static function calculateIRR($cashFlows, $initialInvestment)
    {
        // Combine initial investment with cash flows
        array_unshift($cashFlows, -$initialInvestment);
        
        $irr = 0.1; // Initial guess
        $maxIterations = 100;
        $precision = 0.0001;
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $npv = 0;
            $derivative = 0;
            
            for ($j = 0; $j < count($cashFlows); $j++) {
                $npv += $cashFlows[$j] / pow(1 + $irr, $j);
                
                if ($j > 0) {
                    $derivative += -$j * $cashFlows[$j] / pow(1 + $irr, $j + 1);
                }
            }
            
            $newIrr = $irr - $npv / $derivative;
            
            if (abs($newIrr - $irr) < $precision) {
                return $newIrr * 100; // Convert to percentage
            }
            
            $irr = $newIrr;
        }
        
        throw new \RuntimeException("IRR calculation did not converge");
    }

    /**
     * Calculate Payback Period
     * Time required to recover the initial investment
     */
    public static function calculatePaybackPeriod($initialInvestment, $annualCashFlows)
    {
        $cumulativeCashFlow = 0;
        $period = 0;
        
        foreach ($annualCashFlows as $year => $cashFlow) {
            $cumulativeCashFlow += $cashFlow;
            $period++;
            
            if ($cumulativeCashFlow >= $initialInvestment) {
                // Calculate fractional year if needed
                $remainingAmount = $initialInvestment - ($cumulativeCashFlow - $cashFlow);
                $fractionalYear = $remainingAmount / $cashFlow;
                return $period - 1 + $fractionalYear;
            }
        }
        
        // If investment is never recovered
        return -1;
    }

    /**
     * Calculate Break-even Point (in units)
     * Break-even units = Fixed Costs / (Price per unit - Variable Cost per unit)
     */
    public static function calculateBreakEvenUnits($fixedCosts, $pricePerUnit, $variableCostPerUnit)
    {
        if (($pricePerUnit - $variableCostPerUnit) == 0) {
            throw new \InvalidArgumentException("Price per unit and variable cost per unit cannot be equal");
        }
        
        return $fixedCosts / ($pricePerUnit - $variableCostPerUnit);
    }

    /**
     * Calculate Break-even Point (in revenue)
     * Break-even revenue = Fixed Costs / (1 - (Variable Costs / Revenue))
     */
    public static function calculateBreakEvenRevenue($fixedCosts, $variableCosts, $revenue)
    {
        if ($revenue == 0) {
            throw new \InvalidArgumentException("Revenue cannot be zero");
        }
        
        $variableCostRatio = $variableCosts / $revenue;
        
        if ($variableCostRatio >= 1) {
            throw new \InvalidArgumentException("Variable costs cannot exceed revenue");
        }
        
        return $fixedCosts / (1 - $variableCostRatio);
    }

    /**
     * Calculate Sensitivity Analysis
     * Analyze how changes in key parameters affect the ROI
     */
    public static function calculateSensitivity($baseROI, $parameterChangePercent, $sensitivityFactor)
    {
        // Sensitivity = % change in output / % change in input
        // For this implementation, we'll calculate the new ROI based on parameter change
        $newROI = $baseROI * (1 + ($parameterChangePercent * $sensitivityFactor / 100));
        return $newROI;
    }

    /**
     * Linear regression for forecasting
     * Returns the slope and intercept of the best-fit line
     */
    public static function linearRegression($xValues, $yValues)
    {
        $n = count($xValues);
        
        if ($n !== count($yValues) || $n < 2) {
            throw new \InvalidArgumentException("Need at least 2 data points for regression");
        }
        
        $sumX = array_sum($xValues);
        $sumY = array_sum($yValues);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $xValues[$i] * $yValues[$i];
            $sumX2 += $xValues[$i] * $xValues[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        return ['slope' => $slope, 'intercept' => $intercept];
    }

    /**
     * Moving Average for forecasting
     */
    public static function movingAverage($data, $period = 3)
    {
        if (count($data) < $period) {
            throw new \InvalidArgumentException("Not enough data points for moving average");
        }
        
        $result = [];
        for ($i = $period - 1; $i < count($data); $i++) {
            $sum = 0;
            for ($j = 0; $j < $period; $j++) {
                $sum += $data[$i - $period + 1 + $j];
            }
            $result[] = $sum / $period;
        }
        
        return $result;
    }

    /**
     * Calculate confidence intervals for forecasts
     */
    public static function calculateConfidenceInterval($forecast, $stdDev, $confidenceLevel = 0.95)
    {
        // For simplicity, using a normal approximation
        // Z-score for 95% confidence level is approximately 1.96
        $zScore = 1.96;
        
        if ($confidenceLevel == 0.90) {
            $zScore = 1.645;
        } elseif ($confidenceLevel == 0.99) {
            $zScore = 2.576;
        }
        
        $marginOfError = $zScore * $stdDev;
        
        return [
            'forecast' => $forecast,
            'lower_bound' => $forecast - $marginOfError,
            'upper_bound' => $forecast + $marginOfError
        ];
    }
}
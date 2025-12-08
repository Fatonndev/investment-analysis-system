<?php

namespace App\Controllers;

use App\Models\ProductionData;
use App\Models\CostData;
use App\Models\PriceData;
use App\Models\InvestmentData;
use App\Utils\ROICalculator;

class ROIController
{
    private $productionModel;
    private $costModel;
    private $priceModel;
    private $investmentModel;

    public function __construct()
    {
        $this->productionModel = new ProductionData();
        $this->costModel = new CostData();
        $this->priceModel = new PriceData();
        $this->investmentModel = new InvestmentData();
    }

    /**
     * Calculate ROI metrics based on provided data
     */
    public function calculateROIMetrics($startDate, $endDate, $discountRate = 0.1, $forecastYears = 3)
    {
        // Get production data for the period
        $productionStmt = $this->productionModel->readByDateRange($startDate, $endDate);
        $productionData = $productionStmt->fetchAll();

        // Get cost data for the period
        $costStmt = $this->costModel->readByDateRange($startDate, $endDate);
        $costData = $costStmt->fetchAll();

        // Get price data for the period
        $priceStmt = $this->priceModel->readByDateRange($startDate, $endDate);
        $priceData = $priceStmt->fetchAll();

        // Get investment data
        $investmentStmt = $this->investmentModel->read();
        $investmentData = $investmentStmt->fetchAll();

        // Calculate revenue
        $totalRevenue = $this->calculateRevenue($productionData, $priceData);
        
        // Calculate costs
        $totalCosts = $this->calculateTotalCosts($costData);
        
        // Calculate net profit
        $netProfit = $totalRevenue - $totalCosts;
        
        // Calculate total investments
        $totalInvestment = $this->calculateTotalInvestments($investmentData);

        // Calculate basic ROI
        $roi = ROICalculator::calculateROI($netProfit, $totalInvestment);

        // Calculate NPV
        $cashFlows = $this->generateCashFlows($productionData, $priceData, $costData);
        $npv = ROICalculator::calculateNPV($cashFlows, $discountRate, $totalInvestment);

        // Calculate IRR
        $irr = ROICalculator::calculateIRR($cashFlows, $totalInvestment);

        // Calculate payback period
        $paybackPeriod = ROICalculator::calculatePaybackPeriod($totalInvestment, $cashFlows);

        // Calculate break-even point
        $breakEvenUnits = ROICalculator::calculateBreakEvenUnits(
            $this->getFixedCosts($costData),
            $this->getAveragePrice($priceData),
            $this->getAverageVariableCost($costData)
        );

        // Generate forecasts
        $forecasts = $this->generateForecasts($productionData, $priceData, $costData, $forecastYears);

        return [
            'roi' => $roi,
            'npv' => $npv,
            'irr' => $irr,
            'payback_period' => $paybackPeriod,
            'break_even_units' => $breakEvenUnits,
            'total_revenue' => $totalRevenue,
            'total_costs' => $totalCosts,
            'net_profit' => $netProfit,
            'total_investment' => $totalInvestment,
            'forecasts' => $forecasts,
            'sensitivity_analysis' => $this->performSensitivityAnalysis($roi)
        ];
    }

    /**
     * Calculate total revenue from production and price data
     */
    private function calculateRevenue($productionData, $priceData)
    {
        $totalRevenue = 0;
        
        foreach ($productionData as $production) {
            // Find corresponding price for this product type
            $price = $this->findPriceForProduct($production['product_type'], $priceData);
            if ($price) {
                $totalRevenue += $production['quantity'] * $price['price'];
            }
        }
        
        return $totalRevenue;
    }

    /**
     * Find price for a specific product type
     */
    private function findPriceForProduct($productType, $priceData)
    {
        foreach ($priceData as $price) {
            if ($price['product_type'] === $productType) {
                return $price;
            }
        }
        return null;
    }

    /**
     * Calculate total costs
     */
    private function calculateTotalCosts($costData)
    {
        $totalCosts = 0;
        
        foreach ($costData as $cost) {
            $totalCosts += $cost['amount'];
        }
        
        return $totalCosts;
    }

    /**
     * Calculate total investments
     */
    private function calculateTotalInvestments($investmentData)
    {
        $totalInvestment = 0;
        
        foreach ($investmentData as $investment) {
            $totalInvestment += $investment['amount'];
        }
        
        return $totalInvestment;
    }

    /**
     * Generate cash flows from financial data
     */
    private function generateCashFlows($productionData, $priceData, $costData)
    {
        // For simplicity, we'll calculate annual cash flows
        // In a real application, this would be more complex
        $annualCashFlows = [];
        
        // Group data by year and calculate net cash flow for each year
        $yearlyData = [];
        
        // Process production data
        foreach ($productionData as $production) {
            $year = date('Y', strtotime($production['date']));
            if (!isset($yearlyData[$year])) {
                $yearlyData[$year] = ['revenue' => 0, 'costs' => 0];
            }
            
            $price = $this->findPriceForProduct($production['product_type'], $priceData);
            if ($price) {
                $yearlyData[$year]['revenue'] += $production['quantity'] * $price['price'];
            }
        }
        
        // Process cost data
        foreach ($costData as $cost) {
            $year = date('Y', strtotime($cost['date']));
            if (!isset($yearlyData[$year])) {
                $yearlyData[$year] = ['revenue' => 0, 'costs' => 0];
            }
            
            $yearlyData[$year]['costs'] += $cost['amount'];
        }
        
        // Calculate cash flows
        foreach ($yearlyData as $year => $data) {
            $annualCashFlows[] = $data['revenue'] - $data['costs'];
        }
        
        return $annualCashFlows;
    }

    /**
     * Get fixed costs from cost data
     */
    private function getFixedCosts($costData)
    {
        $fixedCosts = 0;
        
        foreach ($costData as $cost) {
            // In a real application, you would have a field to identify fixed vs variable costs
            // For now, we'll consider all costs as potentially fixed for break-even calculation
            $fixedCosts += $cost['amount'];
        }
        
        return $fixedCosts;
    }

    /**
     * Get average price from price data
     */
    private function getAveragePrice($priceData)
    {
        if (empty($priceData)) {
            return 0;
        }
        
        $totalPrice = 0;
        foreach ($priceData as $price) {
            $totalPrice += $price['price'];
        }
        
        return $totalPrice / count($priceData);
    }

    /**
     * Get average variable cost from cost data
     */
    private function getAverageVariableCost($costData)
    {
        if (empty($costData)) {
            return 0;
        }
        
        $totalCost = 0;
        foreach ($costData as $cost) {
            $totalCost += $cost['amount'];
        }
        
        return $totalCost / count($costData);
    }

    /**
     * Generate forecasts for future periods
     */
    private function generateForecasts($productionData, $priceData, $costData, $years)
    {
        // Simple forecast based on historical averages
        $historicalProduction = $this->getHistoricalAverages($productionData);
        $historicalPrices = $this->getHistoricalAverages($priceData, 'price');
        $historicalCosts = $this->getHistoricalAverages($costData, 'amount');
        
        $forecasts = [
            'optimistic' => [],
            'base' => [],
            'pessimistic' => []
        ];
        
        for ($i = 1; $i <= $years; $i++) {
            // Optimistic scenario: 5% growth
            $forecasts['optimistic'][] = [
                'year' => date('Y') + $i,
                'production' => $historicalProduction * pow(1.05, $i),
                'price' => $historicalPrices * pow(1.03, $i), // 3% price growth
                'cost' => $historicalCosts * pow(1.02, $i)    // 2% cost growth
            ];
            
            // Base scenario: 2% growth
            $forecasts['base'][] = [
                'year' => date('Y') + $i,
                'production' => $historicalProduction * pow(1.02, $i),
                'price' => $historicalPrices * pow(1.02, $i),
                'cost' => $historicalCosts * pow(1.03, $i)    // 3% cost growth
            ];
            
            // Pessimistic scenario: 1% decline
            $forecasts['pessimistic'][] = [
                'year' => date('Y') + $i,
                'production' => $historicalProduction * pow(0.99, $i),
                'price' => $historicalPrices * pow(0.98, $i),
                'cost' => $historicalCosts * pow(1.04, $i)    // 4% cost growth
            ];
        }
        
        return $forecasts;
    }

    /**
     * Get historical averages for forecasting
     */
    private function getHistoricalAverages($data, $field = 'quantity')
    {
        if (empty($data)) {
            return 0;
        }
        
        $sum = 0;
        $count = 0;
        
        foreach ($data as $item) {
            if (isset($item[$field])) {
                $sum += $item[$field];
                $count++;
            }
        }
        
        return $count > 0 ? $sum / $count : 0;
    }

    /**
     * Perform sensitivity analysis
     */
    private function performSensitivityAnalysis($baseRoi)
    {
        return [
            'steel_price_increase_10' => ROICalculator::calculateSensitivity($baseRoi, 10, -0.8), // 10% steel price increase
            'demand_decrease_10' => ROICalculator::calculateSensitivity($baseRoi, -10, -1.2), // 10% demand decrease
            'efficiency_increase_5' => ROICalculator::calculateSensitivity($baseRoi, 5, 0.7), // 5% efficiency increase
        ];
    }
}
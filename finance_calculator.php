<?php
require_once 'config.php';

class FinanceCalculator {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getConnection();
    }
    
    // Function to add production data
    public function addProductionData($period, $product_type, $quantity, $unit_cost, $selling_price) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO production_data (period, product_type, quantity, unit_cost, selling_price) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$period, $product_type, $quantity, $unit_cost, $selling_price]);
        } catch(PDOException $e) {
            error_log("Error adding production data: " . $e->getMessage());
            return false;
        }
    }
    
    // Function to add cost data
    public function addCost($cost_type, $description, $amount, $period) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO costs (cost_type, description, amount, period) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$cost_type, $description, $amount, $period]);
        } catch(PDOException $e) {
            error_log("Error adding cost: " . $e->getMessage());
            return false;
        }
    }
    
    // Function to add market price
    public function addMarketPrice($product_type, $size_spec, $precision_class, $region, $price, $period) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO market_prices (product_type, size_spec, precision_class, region, price, period) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$product_type, $size_spec, $precision_class, $region, $price, $period]);
        } catch(PDOException $e) {
            error_log("Error adding market price: " . $e->getMessage());
            return false;
        }
    }
    
    // Function to add investment
    public function addInvestment($description, $amount, $investment_date) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO investments (description, amount, investment_date) VALUES (?, ?, ?)");
            return $stmt->execute([$description, $amount, $investment_date]);
        } catch(PDOException $e) {
            error_log("Error adding investment: " . $e->getMessage());
            return false;
        }
    }
    
    // Calculate ROI (Return on Investment)
    public function calculateROI($discount_rate = 0.1) {
        try {
            // Get total investments
            $stmt = $this->pdo->query("SELECT SUM(amount) as total_investment FROM investments");
            $investment_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_investment = $investment_result['total_investment'] ?: 0;
            
            // Calculate total profit from production data
            $stmt = $this->pdo->query("SELECT SUM((selling_price - unit_cost) * quantity) as total_profit FROM production_data");
            $profit_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_profit = $profit_result['total_profit'] ?: 0;
            
            if ($total_investment == 0) {
                return 0;
            }
            
            $roi = ($total_profit / $total_investment) * 100;
            return round($roi, 4);
        } catch(PDOException $e) {
            error_log("Error calculating ROI: " . $e->getMessage());
            return 0;
        }
    }
    
    // Calculate NPV (Net Present Value)
    public function calculateNPV($discount_rate = 0.1) {
        try {
            // Get cash flows (profit) by period
            $stmt = $this->pdo->query("
                SELECT period, SUM((selling_price - unit_cost) * quantity) as period_profit 
                FROM production_data 
                GROUP BY period
                ORDER BY period
            ");
            
            $cash_flows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $investments = $this->pdo->query("SELECT SUM(amount) as total_investment FROM investments")->fetch(PDO::FETCH_ASSOC);
            $initial_investment = $investments['total_investment'] ?: 0;
            
            $npv = -$initial_investment;
            $base_year = null;
            
            foreach ($cash_flows as $index => $cf) {
                if ($base_year === null) {
                    $base_year = new DateTime($cf['period']);
                }
                
                $current_date = new DateTime($cf['period']);
                $years_diff = $base_year->diff($current_date)->y + ($base_year->diff($current_date)->m / 12);
                
                $discount_factor = pow(1 + $discount_rate, $years_diff);
                $npv += $cf['period_profit'] / $discount_factor;
            }
            
            return round($npv, 4);
        } catch(PDOException $e) {
            error_log("Error calculating NPV: " . $e->getMessage());
            return 0;
        }
    }
    
    // Calculate IRR (Internal Rate of Return) - simplified approximation
    public function calculateIRR() {
        // Simplified calculation: IRR ≈ (Total Return / Total Investment)^(1/n) - 1
        try {
            $total_investment = $this->pdo->query("SELECT SUM(amount) as total FROM investments")->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
            $total_return = $this->pdo->query("SELECT SUM((selling_price - unit_cost) * quantity) as total FROM production_data")->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
            
            if ($total_investment <= 0) {
                return 0;
            }
            
            // Get the number of periods
            $stmt = $this->pdo->query("SELECT COUNT(DISTINCT period) as periods FROM production_data");
            $periods_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $periods = $periods_result['periods'] ?: 1;
            
            // Simple IRR approximation
            $irr = pow(($total_return / $total_investment) + 1, 1/$periods) - 1;
            return round($irr * 100, 4); // Return as percentage
        } catch(PDOException $e) {
            error_log("Error calculating IRR: " . $e->getMessage());
            return 0;
        }
    }
    
    // Calculate payback period
    public function calculatePaybackPeriod() {
        try {
            $stmt = $this->pdo->query("SELECT SUM(amount) as total_investment FROM investments");
            $investment_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_investment = $investment_result['total_investment'] ?: 0;
            
            if ($total_investment == 0) {
                return 0;
            }
            
            // Get cumulative cash flows by period
            $stmt = $this->pdo->query("
                SELECT period, SUM((selling_price - unit_cost) * quantity) as period_profit 
                FROM production_data 
                GROUP BY period
                ORDER BY period
            ");
            
            $cumulative_cash_flow = 0;
            $payback_period = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cumulative_cash_flow += $row['period_profit'];
                $payback_period++;
                
                if ($cumulative_cash_flow >= $total_investment) {
                    // Interpolate for exact payback period
                    $previous_cumulative = $cumulative_cash_flow - $row['period_profit'];
                    $payback_period = $payback_period - 1 + (($total_investment - $previous_cumulative) / $row['period_profit']);
                    break;
                }
            }
            
            return round($payback_period, 2);
        } catch(PDOException $e) {
            error_log("Error calculating payback period: " . $e->getMessage());
            return 0;
        }
    }
    
    // Calculate break-even point
    public function calculateBreakEven($fixed_costs = null, $variable_cost_per_unit = null) {
        try {
            // If not provided, estimate from data
            if ($fixed_costs === null) {
                $stmt = $this->pdo->query("SELECT SUM(amount) as fixed_costs FROM costs WHERE cost_type IN ('depreciation', 'salary')");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $fixed_costs = $result['fixed_costs'] ?: 0;
            }
            
            if ($variable_cost_per_unit === null) {
                $stmt = $this->pdo->query("SELECT AVG(unit_cost) as avg_variable_cost FROM production_data");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $variable_cost_per_unit = $result['avg_variable_cost'] ?: 0;
            }
            
            // Get average selling price
            $stmt = $this->pdo->query("SELECT AVG(selling_price) as avg_selling_price FROM production_data");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $avg_selling_price = $result['avg_selling_price'] ?: 0;
            
            if (($avg_selling_price - $variable_cost_per_unit) == 0) {
                return 0; // Avoid division by zero
            }
            
            $break_even_units = $fixed_costs / ($avg_selling_price - $variable_cost_per_unit);
            $break_even_revenue = $break_even_units * $avg_selling_price;
            
            return [
                'units' => round($break_even_units, 2),
                'revenue' => round($break_even_revenue, 2)
            ];
        } catch(PDOException $e) {
            error_log("Error calculating break-even: " . $e->getMessage());
            return ['units' => 0, 'revenue' => 0];
        }
    }
    
    // Simple linear regression for forecasting
    public function forecastRevenue($months_ahead = 12) {
        try {
            // Get historical revenue data
            $stmt = $this->pdo->query("
                SELECT period, SUM(selling_price * quantity) as revenue 
                FROM production_data 
                GROUP BY period
                ORDER BY period
            ");
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($data) < 2) {
                return ['error' => 'Not enough data for forecasting'];
            }
            
            // Convert dates to numeric values for regression
            $x_values = [];
            $y_values = [];
            $first_date = new DateTime($data[0]['period']);
            
            foreach ($data as $index => $row) {
                $current_date = new DateTime($row['period']);
                $x_values[] = $first_date->diff($current_date)->days;
                $y_values[] = $row['revenue'];
            }
            
            // Perform simple linear regression: y = a + bx
            $n = count($x_values);
            $sum_x = array_sum($x_values);
            $sum_y = array_sum($y_values);
            $sum_xy = 0;
            $sum_x2 = 0;
            
            for ($i = 0; $i < $n; $i++) {
                $sum_xy += $x_values[$i] * $y_values[$i];
                $sum_x2 += $x_values[$i] * $x_values[$i];
            }
            
            $b = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
            $a = ($sum_y - $b * $sum_x) / $n;
            
            // Generate forecast
            $last_date = new DateTime($data[count($data)-1]['period']);
            $forecast = [];
            
            for ($i = 1; $i <= $months_ahead; $i++) {
                $next_date = clone $last_date;
                $next_date->modify("+$i month");
                
                // Calculate days from first date
                $days_from_start = $first_date->diff($next_date)->days;
                $predicted_revenue = $a + $b * $days_from_start;
                
                $forecast[] = [
                    'period' => $next_date->format('Y-m-d'),
                    'revenue' => max(0, round($predicted_revenue, 2))
                ];
            }
            
            return $forecast;
        } catch(PDOException $e) {
            error_log("Error in revenue forecast: " . $e->getMessage());
            return ['error' => 'Forecast calculation failed'];
        }
    }
    
    // Generate sensitivity analysis
    public function sensitivityAnalysis($steel_price_change = 0.1, $demand_change = -0.05) {
        try {
            // Get baseline profit
            $stmt = $this->pdo->query("SELECT SUM((selling_price - unit_cost) * quantity) as baseline_profit FROM production_data");
            $baseline_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $baseline_profit = $baseline_result['baseline_profit'] ?: 0;
            
            // Calculate new profit with steel price change (affects unit cost)
            $stmt = $this->pdo->query("SELECT SUM(((selling_price * (1 + ?)) - (unit_cost * (1 + ?))) * (quantity * (1 + ?))) as adjusted_profit FROM production_data", [$demand_change, $steel_price_change, $demand_change]);
            $adjusted_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $adjusted_profit = $adjusted_result['adjusted_profit'] ?: 0;
            
            $profit_change = $adjusted_profit - $baseline_profit;
            $profit_change_percent = $baseline_profit != 0 ? ($profit_change / $baseline_profit) * 100 : 0;
            
            return [
                'baseline_profit' => round($baseline_profit, 2),
                'adjusted_profit' => round($adjusted_profit, 2),
                'profit_change' => round($profit_change, 2),
                'profit_change_percent' => round($profit_change_percent, 2),
                'steel_price_change' => $steel_price_change * 100,
                'demand_change' => $demand_change * 100
            ];
        } catch(PDOException $e) {
            error_log("Error in sensitivity analysis: " . $e->getMessage());
            return ['error' => 'Sensitivity analysis failed'];
        }
    }
    
    // Get all production data
    public function getProductionData() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM production_data ORDER BY period DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting production data: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all cost data
    public function getCosts() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM costs ORDER BY period DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting costs: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all market prices
    public function getMarketPrices() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM market_prices ORDER BY period DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting market prices: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all investments
    public function getInvestments() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM investments ORDER BY investment_date DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting investments: " . $e->getMessage());
            return [];
        }
    }
}
?>
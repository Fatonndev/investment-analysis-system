<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steel Pipe Production Financial Analysis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin: 10px 0;
        }
        label {
            display: inline-block;
            width: 200px;
            font-weight: bold;
        }
        input, select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
            width: 200px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #2980b9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        .results {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .chart-container {
            height: 300px;
            margin: 20px 0;
            position: relative;
        }
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0 0;
        }
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
            color: black;
        }
        .tab button:hover {
            background-color: #ddd;
        }
        .tab button.active {
            background-color: #3498db;
            color: white;
        }
        .tabcontent {
            display: none;
            padding: 20px;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Steel Pipe Production Financial Analysis System</h1>
        
        <div class="tab">
            <button class="tablinks active" onclick="openTab(event, 'input')">Data Input</button>
            <button class="tablinks" onclick="openTab(event, 'analysis')">Financial Analysis</button>
            <button class="tablinks" onclick="openTab(event, 'forecast')">Forecasting</button>
            <button class="tablinks" onclick="openTab(event, 'sensitivity')">Sensitivity Analysis</button>
        </div>

        <!-- Data Input Tab -->
        <div id="input" class="tabcontent" style="display:block;">
            <h2>Data Input</h2>
            
            <div class="section">
                <h3>Add Production Data</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_production">
                    <div class="form-group">
                        <label for="period">Period (YYYY-MM-DD):</label>
                        <input type="date" id="period" name="period" required>
                    </div>
                    <div class="form-group">
                        <label for="product_type">Product Type:</label>
                        <input type="text" id="product_type" name="product_type" required>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity (tons):</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="unit_cost">Unit Cost ($):</label>
                        <input type="number" id="unit_cost" name="unit_cost" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="selling_price">Selling Price ($):</label>
                        <input type="number" id="selling_price" name="selling_price" step="0.01" required>
                    </div>
                    <button type="submit">Add Production Data</button>
                </form>
            </div>
            
            <div class="section">
                <h3>Add Cost Data</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_cost">
                    <div class="form-group">
                        <label for="cost_type">Cost Type:</label>
                        <select id="cost_type" name="cost_type" required>
                            <option value="raw_material">Raw Material</option>
                            <option value="energy">Energy</option>
                            <option value="logistics">Logistics</option>
                            <option value="salary">Salary</option>
                            <option value="depreciation">Depreciation</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cost_description">Description:</label>
                        <input type="text" id="cost_description" name="cost_description" required>
                    </div>
                    <div class="form-group">
                        <label for="cost_amount">Amount ($):</label>
                        <input type="number" id="cost_amount" name="cost_amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="cost_period">Period (YYYY-MM-DD):</label>
                        <input type="date" id="cost_period" name="cost_period" required>
                    </div>
                    <button type="submit">Add Cost Data</button>
                </form>
            </div>
            
            <div class="section">
                <h3>Add Market Price</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_market_price">
                    <div class="form-group">
                        <label for="mp_product_type">Product Type:</label>
                        <input type="text" id="mp_product_type" name="product_type" required>
                    </div>
                    <div class="form-group">
                        <label for="size_spec">Size Specification:</label>
                        <input type="text" id="size_spec" name="size_spec">
                    </div>
                    <div class="form-group">
                        <label for="precision_class">Precision Class:</label>
                        <input type="text" id="precision_class" name="precision_class">
                    </div>
                    <div class="form-group">
                        <label for="region">Region:</label>
                        <input type="text" id="region" name="region">
                    </div>
                    <div class="form-group">
                        <label for="price">Price ($):</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="mp_period">Period (YYYY-MM-DD):</label>
                        <input type="date" id="mp_period" name="period" required>
                    </div>
                    <button type="submit">Add Market Price</button>
                </form>
            </div>
            
            <div class="section">
                <h3>Add Investment</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_investment">
                    <div class="form-group">
                        <label for="inv_description">Description:</label>
                        <input type="text" id="inv_description" name="description" required>
                    </div>
                    <div class="form-group">
                        <label for="inv_amount">Amount ($):</label>
                        <input type="number" id="inv_amount" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="inv_date">Investment Date (YYYY-MM-DD):</label>
                        <input type="date" id="inv_date" name="investment_date" required>
                    </div>
                    <button type="submit">Add Investment</button>
                </form>
            </div>
        </div>

        <!-- Financial Analysis Tab -->
        <div id="analysis" class="tabcontent">
            <h2>Financial Analysis</h2>
            
            <?php
            require_once 'finance_calculator.php';
            
            $calculator = new FinanceCalculator();
            
            // Handle form submissions
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? '';
                
                switch ($action) {
                    case 'add_production':
                        $success = $calculator->addProductionData(
                            $_POST['period'],
                            $_POST['product_type'],
                            $_POST['quantity'],
                            $_POST['unit_cost'],
                            $_POST['selling_price']
                        );
                        if ($success) {
                            echo '<div class="success">Production data added successfully!</div>';
                        } else {
                            echo '<div class="error">Error adding production data!</div>';
                        }
                        break;
                        
                    case 'add_cost':
                        $success = $calculator->addCost(
                            $_POST['cost_type'],
                            $_POST['cost_description'],
                            $_POST['cost_amount'],
                            $_POST['cost_period']
                        );
                        if ($success) {
                            echo '<div class="success">Cost data added successfully!</div>';
                        } else {
                            echo '<div class="error">Error adding cost data!</div>';
                        }
                        break;
                        
                    case 'add_market_price':
                        $success = $calculator->addMarketPrice(
                            $_POST['product_type'],
                            $_POST['size_spec'],
                            $_POST['precision_class'],
                            $_POST['region'],
                            $_POST['price'],
                            $_POST['period']
                        );
                        if ($success) {
                            echo '<div class="success">Market price added successfully!</div>';
                        } else {
                            echo '<div class="error">Error adding market price!</div>';
                        }
                        break;
                        
                    case 'add_investment':
                        $success = $calculator->addInvestment(
                            $_POST['description'],
                            $_POST['amount'],
                            $_POST['investment_date']
                        );
                        if ($success) {
                            echo '<div class="success">Investment added successfully!</div>';
                        } else {
                            echo '<div class="error">Error adding investment!</div>';
                        }
                        break;
                }
            }
            
            // Calculate financial metrics
            $roi = $calculator->calculateROI();
            $npv = $calculator->calculateNPV();
            $irr = $calculator->calculateIRR();
            $payback_period = $calculator->calculatePaybackPeriod();
            $break_even = $calculator->calculateBreakEven();
            ?>
            
            <div class="results">
                <h3>Financial Metrics</h3>
                <table>
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td>Return on Investment (ROI)</td>
                        <td><?php echo number_format($roi, 2); ?>%</td>
                    </tr>
                    <tr>
                        <td>Net Present Value (NPV)</td>
                        <td>$<?php echo number_format($npv, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Internal Rate of Return (IRR)</td>
                        <td><?php echo number_format($irr, 2); ?>%</td>
                    </tr>
                    <tr>
                        <td>Payback Period (years)</td>
                        <td><?php echo number_format($payback_period, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Break-even Units</td>
                        <td><?php echo number_format($break_even['units'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Break-even Revenue ($)</td>
                        <td>$<?php echo number_format($break_even['revenue'], 2); ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="section">
                <h3>Production Data</h3>
                <table>
                    <tr>
                        <th>Period</th>
                        <th>Product Type</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Selling Price</th>
                        <th>Profit</th>
                    </tr>
                    <?php
                    $production_data = $calculator->getProductionData();
                    foreach ($production_data as $row) {
                        $profit = ($row['selling_price'] - $row['unit_cost']) * $row['quantity'];
                        echo "<tr>";
                        echo "<td>" . $row['period'] . "</td>";
                        echo "<td>" . $row['product_type'] . "</td>";
                        echo "<td>" . number_format($row['quantity'], 2) . "</td>";
                        echo "<td>$" . number_format($row['unit_cost'], 2) . "</td>";
                        echo "<td>$" . number_format($row['selling_price'], 2) . "</td>";
                        echo "<td>$" . number_format($profit, 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
            
            <div class="section">
                <h3>Cost Data</h3>
                <table>
                    <tr>
                        <th>Period</th>
                        <th>Cost Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                    <?php
                    $costs = $calculator->getCosts();
                    foreach ($costs as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['period'] . "</td>";
                        echo "<td>" . ucfirst(str_replace('_', ' ', $row['cost_type'])) . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

        <!-- Forecasting Tab -->
        <div id="forecast" class="tabcontent">
            <h2>Forecasting</h2>
            
            <div class="section">
                <h3>Revenue Forecast (Next 12 Months)</h3>
                <?php
                $forecast = $calculator->forecastRevenue(12);
                if (isset($forecast['error'])) {
                    echo '<div class="error">' . $forecast['error'] . '</div>';
                } else {
                    echo '<table>';
                    echo '<tr><th>Period</th><th>Forecasted Revenue</th></tr>';
                    foreach ($forecast as $row) {
                        echo '<tr>';
                        echo '<td>' . $row['period'] . '</td>';
                        echo '<td>$' . number_format($row['revenue'], 2) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                ?>
            </div>
            
            <div class="section">
                <h3>Market Prices</h3>
                <table>
                    <tr>
                        <th>Period</th>
                        <th>Product Type</th>
                        <th>Size Spec</th>
                        <th>Region</th>
                        <th>Price</th>
                    </tr>
                    <?php
                    $market_prices = $calculator->getMarketPrices();
                    foreach ($market_prices as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['period'] . "</td>";
                        echo "<td>" . $row['product_type'] . "</td>";
                        echo "<td>" . $row['size_spec'] . "</td>";
                        echo "<td>" . $row['region'] . "</td>";
                        echo "<td>$" . number_format($row['price'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

        <!-- Sensitivity Analysis Tab -->
        <div id="sensitivity" class="tabcontent">
            <h2>Sensitivity Analysis</h2>
            
            <div class="section">
                <h3>Impact of Steel Price and Demand Changes</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="sensitivity_analysis">
                    <div class="form-group">
                        <label for="steel_change">Steel Price Change (%):</label>
                        <input type="number" id="steel_change" name="steel_change" value="10" step="0.1"> 
                    </div>
                    <div class="form-group">
                        <label for="demand_change">Demand Change (%):</label>
                        <input type="number" id="demand_change" name="demand_change" value="-5" step="0.1">
                    </div>
                    <button type="submit">Run Sensitivity Analysis</button>
                </form>
                
                <?php
                if (isset($_POST['action']) && $_POST['action'] === 'sensitivity_analysis') {
                    $steel_change = ($_POST['steel_change'] ?? 10) / 100;
                    $demand_change = ($_POST['demand_change'] ?? -5) / 100;
                    
                    $sensitivity = $calculator->sensitivityAnalysis($steel_change, $demand_change);
                    if (isset($sensitivity['error'])) {
                        echo '<div class="error">' . $sensitivity['error'] . '</div>';
                    } else {
                        echo '<div class="results">';
                        echo '<h3>Analysis Results</h3>';
                        echo '<table>';
                        echo '<tr><th>Metric</th><th>Value</th></tr>';
                        echo '<tr><td>Baseline Profit</td><td>$' . number_format($sensitivity['baseline_profit'], 2) . '</td></tr>';
                        echo '<tr><td>Adjusted Profit</td><td>$' . number_format($sensitivity['adjusted_profit'], 2) . '</td></tr>';
                        echo '<tr><td>Profit Change</td><td>$' . number_format($sensitivity['profit_change'], 2) . '</td></tr>';
                        echo '<tr><td>Profit Change (%)</td><td>' . number_format($sensitivity['profit_change_percent'], 2) . '%</td></tr>';
                        echo '<tr><td>Steel Price Change</td><td>' . number_format($sensitivity['steel_price_change'], 2) . '%</td></tr>';
                        echo '<tr><td>Demand Change</td><td>' . number_format($sensitivity['demand_change'], 2) . '%</td></tr>';
                        echo '</table>';
                        echo '</div>';
                    }
                } else {
                    $sensitivity = $calculator->sensitivityAnalysis(0.1, -0.05);
                    if (!isset($sensitivity['error'])) {
                        echo '<div class="results">';
                        echo '<h3>Default Analysis Results (10% steel price increase, -5% demand)</h3>';
                        echo '<table>';
                        echo '<tr><th>Metric</th><th>Value</th></tr>';
                        echo '<tr><td>Baseline Profit</td><td>$' . number_format($sensitivity['baseline_profit'], 2) . '</td></tr>';
                        echo '<tr><td>Adjusted Profit</td><td>$' . number_format($sensitivity['adjusted_profit'], 2) . '</td></tr>';
                        echo '<tr><td>Profit Change</td><td>$' . number_format($sensitivity['profit_change'], 2) . '</td></tr>';
                        echo '<tr><td>Profit Change (%)</td><td>' . number_format($sensitivity['profit_change_percent'], 2) . '%</td></tr>';
                        echo '</table>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            
            <div class="section">
                <h3>Investments</h3>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                    <?php
                    $investments = $calculator->getInvestments();
                    foreach ($investments as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['investment_date'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
        
        // Set today's date as default for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                if (!input.value) {
                    input.value = today;
                }
            });
        });
    </script>
</body>
</html>
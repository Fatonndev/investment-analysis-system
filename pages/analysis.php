<div class="analysis-page">
    <h2>Анализ проекта</h2>
    
    <?php
    $projectId = $_GET['project_id'] ?? 0;
    
    if ($projectId == 0) {
        echo "<p>Пожалуйста, выберите проект для анализа.</p>";
        echo "<p><a href='?action=projects'>Выбрать проект</a></p>";
        return;
    }
    
    $project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$projectId]);
    if (!$project) {
        echo "<p>Проект не найден.</p>";
        return;
    }
    
    echo "<h3>Проект: " . htmlspecialchars($project['name']) . "</h3>";
    
    // Get analysis parameters from GET request
    $discountRate = isset($_GET['discount_rate']) ? floatval($_GET['discount_rate']) : 10;
    $forecastYears = isset($_GET['forecast_years']) ? intval($_GET['forecast_years']) : 3;
    
    // Perform analysis with parameters
    $analysisResults = $analysis->calculateProjectAnalysis($projectId, $discountRate / 100, $forecastYears);
    
    if (isset($analysisResults['error'])) {
        echo "<div class='alert-error'>" . $analysisResults['error'] . "</div>";
        echo "<p><a href='?action=data-input&project_id=$projectId'>Добавить данные для анализа</a></p>";
        return;
    }
    ?>
    
    <div class="analysis-parameters">
        <h3>Параметры анализа</h3>
        <form method="GET" action="?action=analysis">
            <input type="hidden" name="project_id" value="<?php echo $projectId; ?>">
            <input type="hidden" name="action" value="analysis">
            <div class="form-row">
                <div class="form-group">
                    <label for="discount_rate">Ставка дисконтирования (%):</label>
                    <input type="number" id="discount_rate" name="discount_rate" value="<?php echo $discountRate; ?>" min="0" max="100" step="0.1">
                </div>
                <div class="form-group">
                    <label for="forecast_years">Горизонт прогнозирования (лет):</label>
                    <input type="number" id="forecast_years" name="forecast_years" value="<?php echo $forecastYears; ?>" min="1" max="10">
                </div>
            </div>
            <button type="submit" class="btn-primary">Пересчитать</button>
        </form>
    </div>
    
    <div class="analysis-results">
        <h3>Результаты анализа</h3>
        
        <div class="metrics-grid">
            <div class="metric-card">
                <h4>ROI (Рентабельность инвестиций)</h4>
                <p class="metric-value" id="roi-value"><?php echo number_format($analysisResults['roi'], 2); ?>%</p>
                <p class="metric-desc">Доходность на вложенный капитал</p>
            </div>
            
            <div class="metric-card">
                <h4>NPV (Чистая приведенная стоимость)</h4>
                <p class="metric-value" id="npv-value"><?php echo number_format($analysisResults['npv'], 2, '.', ' '); ?> руб.</p>
                <p class="metric-desc">Приведенная стоимость будущих денежных потоков</p>
            </div>
            
            <div class="metric-card">
                <h4>IRR (Внутренняя норма доходности)</h4>
                <p class="metric-value" id="irr-value"><?php echo number_format($analysisResults['irr'] * 100, 2); ?>%</p>
                <p class="metric-desc">Ставка дисконтирования, при которой NPV равен 0</p>
            </div>
            
            <div class="metric-card">
                <h4>Срок окупаемости</h4>
                <p class="metric-value" id="payback-value"><?php 
                    if ($analysisResults['payback_period'] > 0) {
                        echo number_format($analysisResults['payback_period'], 2) . ' лет';
                    } else {
                        echo 'Не окупается';
                    }
                ?></p>
                <p class="metric-desc">Время возврата инвестиций</p>
            </div>
        </div>
        
        <div class="financial-summary">
            <h4>Финансовое состояние проекта</h4>
            <table class="data-table">
                <tr>
                    <td><strong>Общая выручка:</strong></td>
                    <td><?php echo number_format($analysisResults['total_revenue'], 2, '.', ' '); ?> руб.</td>
                </tr>
                <tr>
                    <td><strong>Общие затраты:</strong></td>
                    <td><?php echo number_format($analysisResults['total_costs'], 2, '.', ' '); ?> руб.</td>
                </tr>
                <tr>
                    <td><strong>Общая прибыль:</strong></td>
                    <td><?php echo number_format($analysisResults['total_profit'], 2, '.', ' '); ?> руб.</td>
                </tr>
                <tr>
                    <td><strong>Общие инвестиции:</strong></td>
                    <td><?php echo number_format($analysisResults['total_investment'], 2, '.', ' '); ?> руб.</td>
                </tr>
            </table>
        </div>
        
        <!-- Cash Flow Chart -->
        <div class="chart-container">
            <h4>Денежные потоки по месяцам</h4>
            <canvas id="cashFlowChart" width="400" height="200"></canvas>
            <div id="chart-loading" style="display:none; text-align: center; padding: 20px;">
                <p>Загрузка данных графика...</p>
            </div>
        </div>
        
        <!-- Sensitivity Analysis -->
        <div class="sensitivity-analysis">
            <h4>Анализ чувствительности</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Изменение параметров (%)</th>
                        <th>Выручка</th>
                        <th>Затраты</th>
                        <th>Прибыль</th>
                        <th>ROI (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($analysisResults['sensitivity_analysis'] as $result) {
                        echo "<tr>";
                        echo "<td>" . $result['change_percent'] . "%</td>";
                        echo "<td>" . number_format($result['revenue'], 2, '.', ' ') . "</td>";
                        echo "<td>" . number_format($result['costs'], 2, '.', ' ') . "</td>";
                        echo "<td>" . number_format($result['profit'], 2, '.', ' ') . "</td>";
                        echo "<td>" . number_format($result['roi'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Forecast Scenarios -->
        <div class="forecast-scenarios">
            <h4>Прогнозные сценарии (на <?php echo $forecastYears; ?> года)</h4>
            <div class="chart-container">
                <canvas id="forecastChart" width="400" height="200"></canvas>
            </div>
            
            <div class="scenarios-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Год</th>
                            <th>Оптимистичный сценарий</th>
                            <th>Базовый сценарий</th>
                            <th>Пессимистичный сценарий</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($i = 1; $i <= $forecastYears; $i++) {
                            echo "<tr>";
                            echo "<td>Год " . $i . "</td>";
                            echo "<td>" . number_format($analysisResults['forecast_scenarios']['optimistic'][$i-1] ?? 0, 2, '.', ' ') . " руб.</td>";
                            echo "<td>" . number_format($analysisResults['forecast_scenarios']['base'][$i-1] ?? 0, 2, '.', ' ') . " руб.</td>";
                            echo "<td>" . number_format($analysisResults['forecast_scenarios']['pessimistic'][$i-1] ?? 0, 2, '.', ' ') . " руб.</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Cash flow chart
document.addEventListener('DOMContentLoaded', function() {
    const projectId = <?php echo $projectId; ?>;
    const discountRate = <?php echo $discountRate; ?>;
    const forecastYears = <?php echo $forecastYears; ?>;
    
    // Load cash flow chart data
    loadCashFlowChart(projectId, discountRate, forecastYears);
    
    // Forecast chart
    const ctx2 = document.getElementById('forecastChart').getContext('2d');
    const forecastScenarios = <?php echo json_encode($analysisResults['forecast_scenarios']); ?>;
    
    // Generate labels dynamically based on forecast years
    const forecastLabels = [];
    for (let i = 1; i <= <?php echo $forecastYears; ?>; i++) {
        forecastLabels.push('Год ' + i);
    }
    
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: forecastLabels,
            datasets: [
                {
                    label: 'Оптимистичный сценарий',
                    data: forecastScenarios.optimistic,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Базовый сценарий',
                    data: forecastScenarios.base,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1
                },
                {
                    label: 'Пессимистичный сценарий',
                    data: forecastScenarios.pessimistic,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

// Function to load cash flow chart data from external PHP file
function loadCashFlowChart(projectId, discountRate, forecastYears) {
    const ctx1 = document.getElementById('cashFlowChart').getContext('2d');
    
    // Show loading indicator
    document.getElementById('chart-loading').style.display = 'block';
    
    // Fetch data from external PHP file
    fetch('get_cashflow_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `project_id=${projectId}&discount_rate=${discountRate}&forecast_years=${forecastYears}`
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading indicator
        document.getElementById('chart-loading').style.display = 'none';
        
        if (data.success) {
            // Destroy existing chart instance if it exists
            if (window.cashFlowChartInstance) {
                window.cashFlowChartInstance.destroy();
            }
            
            // Create new chart with received data
            window.cashFlowChartInstance = new Chart(ctx1, {
                type: 'bar',
                data: data.data,
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toLocaleString('ru-RU') + ' руб.';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.error('Error loading chart data:', data.error);
            // Display error message on the chart area
            ctx1.clearRect(0, 0, ctx1.canvas.width, ctx1.canvas.height);
            ctx1.font = '14px Arial';
            ctx1.fillStyle = '#ff0000';
            ctx1.textAlign = 'center';
            ctx1.fillText('Ошибка загрузки данных графика: ' + data.error, ctx1.canvas.width/2, ctx1.canvas.height/2);
        }
    })
    .catch(error => {
        // Hide loading indicator
        document.getElementById('chart-loading').style.display = 'none';
        console.error('Error fetching chart data:', error);
        
        // Display error message on the chart area
        const ctx1 = document.getElementById('cashFlowChart').getContext('2d');
        ctx1.clearRect(0, 0, ctx1.canvas.width, ctx1.canvas.height);
        ctx1.font = '14px Arial';
        ctx1.fillStyle = '#ff0000';
        ctx1.textAlign = 'center';
        ctx1.fillText('Ошибка подключения к серверу данных', ctx1.canvas.width/2, ctx1.canvas.height/2);
    });
}

// Function to refresh the cash flow chart when parameters change
function refreshCashFlowChart() {
    const projectId = <?php echo $projectId; ?>;
    const discountRate = document.getElementById('discount_rate').value;
    const forecastYears = document.getElementById('forecast_years').value;
    
    loadCashFlowChart(projectId, discountRate, forecastYears);
}

// Dynamic calculation functionality
function updateMetrics() {
    const discountRate = document.getElementById('discount_rate').value;
    const forecastYears = document.getElementById('forecast_years').value;
    const projectId = <?php echo $projectId; ?>;
    
    // Show loading state
    document.getElementById('roi-value').textContent = 'Расчет...';
    document.getElementById('npv-value').textContent = 'Расчет...';
    document.getElementById('irr-value').textContent = 'Расчет...';
    
    fetch('calculate_metrics.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `project_id=${projectId}&discount_rate=${discountRate}&forecast_years=${forecastYears}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('roi-value').textContent = data.roi.toFixed(2) + '%';
            // Format NPV with space as thousands separator like in PHP
            document.getElementById('npv-value').textContent = parseFloat(data.npv).toLocaleString('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).replace(/\s/g, ' ') + ' руб.';
            document.getElementById('irr-value').textContent = (data.irr * 100).toFixed(2) + '%';
            
            // Refresh cash flow chart with new parameters
            refreshCashFlowChart();
        } else {
            console.error('Error calculating metrics:', data.error);
            document.getElementById('roi-value').textContent = 'Ошибка';
            document.getElementById('npv-value').textContent = 'Ошибка';
            document.getElementById('irr-value').textContent = 'Ошибка';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('roi-value').textContent = 'Ошибка';
        document.getElementById('npv-value').textContent = 'Ошибка';
        document.getElementById('irr-value').textContent = 'Ошибка';
    });
}


// Set up event listeners for real-time updates
document.getElementById('discount_rate').addEventListener('input', updateMetrics);
document.getElementById('forecast_years').addEventListener('input', updateMetrics);

// Initial setup after page loads
updateMetrics();

</script>
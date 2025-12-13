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
    
    // Perform analysis
    $analysisResults = $analysis->calculateProjectAnalysis($projectId);
    
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
                    <input type="number" id="discount_rate" name="discount_rate" value="10" min="0" max="100" step="0.1">
                </div>
                <div class="form-group">
                    <label for="forecast_years">Горизонт прогнозирования (лет):</label>
                    <input type="number" id="forecast_years" name="forecast_years" value="3" min="1" max="10">
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
                <p class="metric-value"><?php echo number_format($analysisResults['roi'], 2); ?>%</p>
                <p class="metric-desc">Доходность на вложенный капитал</p>
            </div>
            
            <div class="metric-card">
                <h4>NPV (Чистая приведенная стоимость)</h4>
                <p class="metric-value"><?php echo number_format($analysisResults['npv'], 2, '.', ' '); ?> руб.</p>
                <p class="metric-desc">Приведенная стоимость будущих денежных потоков</p>
            </div>
            
            <div class="metric-card">
                <h4>IRR (Внутренняя норма доходности)</h4>
                <p class="metric-value"><?php echo number_format($analysisResults['irr'] * 100, 2); ?>%</p>
                <p class="metric-desc">Ставка дисконтирования, при которой NPV равен 0</p>
            </div>
            
            <div class="metric-card">
                <h4>Срок окупаемости</h4>
                <p class="metric-value"><?php 
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
            <h4>Денежные потоки по периодам</h4>
            <canvas id="cashFlowChart" width="400" height="200"></canvas>
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
            <h4>Прогнозные сценарии (на 3 года)</h4>
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
                        $forecastYears = 3;
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
    const ctx1 = document.getElementById('cashFlowChart').getContext('2d');
    const cashFlows = <?php echo json_encode($analysisResults['cash_flows']); ?>;
    
    // Prepare data for chart - separate investments and revenues
    const labels = [];
    const investmentData = []; // For negative values (investments)
    const revenueData = []; // For positive values (revenues)
    
    // Add initial investment
    labels.push('Инвестиции');
    if (cashFlows[0] < 0) {
        investmentData.push(cashFlows[0]);
        revenueData.push(0);
    } else {
        investmentData.push(0);
        revenueData.push(cashFlows[0]);
    }
    
    // Add monthly flows
    for (let i = 1; i < cashFlows.length; i++) {
        labels.push('Месяц ' + i);
        if (cashFlows[i] < 0) {
            investmentData.push(cashFlows[i]); // Negative values (investments/expenses)
            revenueData.push(0);
        } else {
            investmentData.push(0);
            revenueData.push(cashFlows[i]); // Positive values (incomes/revenues)
        }
    }
    
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Доходы (руб.)',
                    data: revenueData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Инвестиции (руб.)',
                    data: investmentData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
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
    
    // Forecast chart
    const ctx2 = document.getElementById('forecastChart').getContext('2d');
    const forecastScenarios = <?php echo json_encode($analysisResults['forecast_scenarios']); ?>;
    
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: ['Год 1', 'Год 2', 'Год 3'],
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
</script>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система анализа и прогнозирования рентабельности инвестиций</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background: linear-gradient(135deg, #0066cc, #003366);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .metric-card {
            text-align: center;
            padding: 1.5rem;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #0066cc;
        }
        .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .chart-container {
            position: relative;
            height: 400px;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1 class="text-center">Система анализа и прогнозирования рентабельности инвестиций</h1>
            <p class="text-center mb-0">Трубопрокатный завод</p>
        </div>
    </div>

    <div class="container">
        <!-- Main Metrics Section -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="metric-value" id="roi-value">0%</div>
                    <div class="metric-label">ROI</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="metric-value" id="npv-value">$0</div>
                    <div class="metric-label">NPV</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="metric-value" id="irr-value">0%</div>
                    <div class="metric-label">IRR</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="metric-value" id="payback-value">0</div>
                    <div class="metric-label">Срок окупаемости (лет)</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="metric-value" id="revenue-value">$0</div>
                    <div class="metric-label">Выручка</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card metric-card">
                    <div class="metric-value" id="profit-value">$0</div>
                    <div class="metric-label">Прибыль</div>
                </div>
            </div>
        </div>

        <!-- Input Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Параметры анализа</h5>
            </div>
            <div class="card-body">
                <form id="analysis-form">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="start-date" class="form-label">Начальная дата</label>
                            <input type="date" class="form-control" id="start-date" value="2023-01-01">
                        </div>
                        <div class="col-md-3">
                            <label for="end-date" class="form-label">Конечная дата</label>
                            <input type="date" class="form-control" id="end-date" value="2023-12-31">
                        </div>
                        <div class="col-md-3">
                            <label for="discount-rate" class="form-label">Ставка дисконтирования</label>
                            <input type="number" class="form-control" id="discount-rate" step="0.01" value="0.1" min="0" max="1">
                        </div>
                        <div class="col-md-3">
                            <label for="forecast-years" class="form-label">Горизонт прогнозирования (лет)</label>
                            <input type="number" class="form-control" id="forecast-years" value="3" min="1" max="10">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" id="calculate-btn">Рассчитать показатели</button>
                        <button type="button" class="btn btn-success" id="export-btn">Экспорт отчета</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Прогноз выручки и прибыли</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="forecastChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Структура затрат</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="costChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sensitivity Analysis -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Анализ чувствительности</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="metric-value" id="sens-steel-value">0%</div>
                            <div class="metric-label">+10% цена стали</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="metric-value" id="sens-demand-value">0%</div>
                            <div class="metric-label">-10% спрос</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="metric-value" id="sens-efficiency-value">0%</div>
                            <div class="metric-label">+5% эффективность</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card metric-card">
                            <div class="metric-value" id="break-even-value">0</div>
                            <div class="metric-label">Точка безубыточности</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize charts
        let forecastChart, costChart;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts with sample data
            initCharts();
            
            // Calculate button event
            document.getElementById('calculate-btn').addEventListener('click', calculateMetrics);
            
            // Export button event
            document.getElementById('export-btn').addEventListener('click', exportReport);
        });
        
        function initCharts() {
            // Forecast chart
            const forecastCtx = document.getElementById('forecastChart').getContext('2d');
            forecastChart = new Chart(forecastCtx, {
                type: 'line',
                data: {
                    labels: ['2023', '2024', '2025', '2026'],
                    datasets: [
                        {
                            label: 'Выручка (оптимистичный)',
                            data: [5000000, 5250000, 5512500, 5788125],
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'Выручка (базовый)',
                            data: [5000000, 5100000, 5202000, 5306040],
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'Выручка (пессимистичный)',
                            data: [5000000, 4950000, 4900500, 4851495],
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.1
                        },
                        {
                            label: 'Прибыль (оптимистичный)',
                            data: [1000000, 1100000, 1210000, 1331000],
                            borderColor: 'rgb(75, 192, 192)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.1
                        },
                        {
                            label: 'Прибыль (базовый)',
                            data: [1000000, 1020000, 1040400, 1061208],
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.1
                        },
                        {
                            label: 'Прибыль (пессимистичный)',
                            data: [1000000, 980000, 960400, 941192],
                            borderColor: 'rgb(255, 99, 132)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Cost chart
            const costCtx = document.getElementById('costChart').getContext('2d');
            costChart = new Chart(costCtx, {
                type: 'pie',
                data: {
                    labels: ['Сырье (сталь)', 'Энергоносители', 'Зарплата', 'Амортизация', 'Прочее'],
                    datasets: [{
                        data: [45, 20, 15, 10, 10],
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(153, 102, 255)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
        
        function calculateMetrics() {
            // Get form values
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            const discountRate = parseFloat(document.getElementById('discount-rate').value);
            const forecastYears = parseInt(document.getElementById('forecast-years').value);
            
            // Make AJAX call to the backend
            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    start_date: startDate,
                    end_date: endDate,
                    discount_rate: discountRate,
                    forecast_years: forecastYears
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update metrics with calculated values
                    document.getElementById('roi-value').textContent = data.data.roi.toFixed(2) + '%';
                    document.getElementById('npv-value').textContent = '$' + data.data.npv.toLocaleString();
                    document.getElementById('irr-value').textContent = data.data.irr.toFixed(2) + '%';
                    document.getElementById('payback-value').textContent = data.data.payback_period.toFixed(1);
                    document.getElementById('revenue-value').textContent = '$' + data.data.total_revenue.toLocaleString();
                    document.getElementById('profit-value').textContent = '$' + data.data.net_profit.toLocaleString();
                    
                    // Update sensitivity analysis
                    document.getElementById('sens-steel-value').textContent = data.data.sensitivity_analysis.steel_price_increase_10.toFixed(2) + '%';
                    document.getElementById('sens-demand-value').textContent = data.data.sensitivity_analysis.demand_decrease_10.toFixed(2) + '%';
                    document.getElementById('sens-efficiency-value').textContent = data.data.sensitivity_analysis.efficiency_increase_5.toFixed(2) + '%';
                    document.getElementById('break-even-value').textContent = data.data.break_even_units.toFixed(0) + ' тонн';
                    
                    alert('Показатели успешно рассчитаны!');
                } else {
                    alert('Ошибка при расчете показателей: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при соединении с сервером');
            });
        }
        
        function exportReport() {
            alert('Функция экспорта отчета в PDF/Excel. В реальном приложении генерируется отчет.');
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
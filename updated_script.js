    <script>
    // Cash flow chart
    document.addEventListener('DOMContentLoaded', function() {
        const ctx1 = document.getElementById('cashFlowChart').getContext('2d');
        const cashFlows = <?php echo json_encode($analysisResults['cash_flows']); ?>;
        const periods = <?php echo json_encode($analysisResults['periods']); ?>;
        const initialInvestments = <?php echo json_encode($analysisResults['initial_investments']); ?>;
        const operationalCashFlows = <?php echo json_encode($analysisResults['operational_cash_flows']); ?>;
        
        // Prepare data for chart - separate investments and revenues
        const labels = ['Инвестиции'];
        const investmentData = [<?php echo -$analysisResults['initial_investments']; ?>]; // Initial investments
        const revenueData = [0]; // No revenue in initial period
        
        // Process operational cash flows by period
        for (let i = 0; i < operationalCashFlows.length; i++) {
            labels.push('Месяц ' + (i + 1));
            
            // Split operational cash flow into investment component and revenue/cost component
            // Since operationalCashFlows already includes both profits and investments in each period,
            // we need to extract pure investments from each period
            if (operationalCashFlows[i] < 0) {
                // Negative value means net outflow (could be investments or costs exceeding revenue)
                investmentData.push(operationalCashFlows[i]);
                revenueData.push(0);
            } else {
                // Positive value means net inflow (revenue exceeding costs)
                investmentData.push(0);
                revenueData.push(operationalCashFlows[i]);
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
                        beginAtZero: false  // Allow negative values
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
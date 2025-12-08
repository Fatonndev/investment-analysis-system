<div class="reports-page">
    <h2>Отчеты</h2>
    
    <div class="reports-actions">
        <button id="generate-report-btn" class="btn-primary">Сформировать отчет</button>
        <select id="report-type">
            <option value="financial">Финансовый отчет</option>
            <option value="analysis">Отчет анализа</option>
            <option value="forecast">Прогнозный отчет</option>
            <option value="comparison">Сравнительный отчет</option>
        </select>
    </div>
    
    <div id="report-params" style="display: none; margin: 20px 0;">
        <h3>Параметры отчета</h3>
        <form id="report-form" method="POST" action="generate_report.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="report_project">Проект:</label>
                    <select id="report_project" name="project_id">
                        <option value="">Все проекты</option>
                        <?php
                        $projects = $db->fetchAll("SELECT * FROM projects ORDER BY name");
                        foreach ($projects as $project) {
                            echo "<option value='" . $project['id'] . "'>" . htmlspecialchars($project['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="report_period">Период:</label>
                    <select id="report_period" name="period_type">
                        <option value="monthly">Ежемесячно</option>
                        <option value="quarterly">Ежеквартально</option>
                        <option value="yearly">Ежегодно</option>
                        <option value="custom">Произвольный</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Дата начала:</label>
                    <input type="date" id="start_date" name="start_date">
                </div>
                <div class="form-group">
                    <label for="end_date">Дата окончания:</label>
                    <input type="date" id="end_date" name="end_date">
                </div>
            </div>
            
            <div class="form-group">
                <label for="report_format">Формат отчета:</label>
                <select id="report_format" name="format">
                    <option value="html">HTML</option>
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary">Сформировать отчет</button>
        </form>
    </div>
    
    <div class="existing-reports">
        <h3>Существующие отчеты</h3>
        <p>В текущей реализации сохранение отчетов в файлы требует дополнительных библиотек.</p>
        <p>Для полной функциональности экспорта в PDF/Excel рекомендуется использовать библиотеки:</p>
        <ul>
            <li>Для PDF: TCPDF или FPDF</li>
            <li>Для Excel: PhpSpreadsheet</li>
        </ul>
        
        <div class="report-samples">
            <h4>Пример отчета по проекту</h4>
            <?php
            // Show a sample report if a project is selected
            $reportProjectId = $_POST['project_id'] ?? 0;
            if ($reportProjectId > 0) {
                $project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$reportProjectId]);
                if ($project) {
                    echo "<h5>Проект: " . htmlspecialchars($project['name']) . "</h5>";
                    
                    // Perform analysis for sample report
                    $analysisResults = $analysis->calculateProjectAnalysis($reportProjectId);
                    if (!isset($analysisResults['error'])) {
                        echo "<div class='sample-report'>";
                        echo "<p><strong>ROI:</strong> " . number_format($analysisResults['roi'], 2) . "%</p>";
                        echo "<p><strong>NPV:</strong> " . number_format($analysisResults['npv'], 2, '.', ' ') . " руб.</p>";
                        echo "<p><strong>IRR:</strong> " . number_format($analysisResults['irr'] * 100, 2) . "%</p>";
                        echo "<p><strong>Срок окупаемости:</strong> ";
                        if ($analysisResults['payback_period'] > 0) {
                            echo number_format($analysisResults['payback_period'], 2) . " лет";
                        } else {
                            echo "Не окупается";
                        }
                        echo "</p>";
                        echo "</div>";
                    }
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-report-btn');
    const reportParams = document.getElementById('report-params');
    
    generateBtn.addEventListener('click', function() {
        reportParams.style.display = 'block';
    });
    
    // Set default dates
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').valueAsDate = firstDay;
    document.getElementById('end_date').valueAsDate = today;
});
</script>
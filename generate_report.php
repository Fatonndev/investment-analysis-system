<?php
// Report generation script
require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/calculations.php';

$db = new Database();
$analysis = new InvestmentAnalysis();

// Get form data
$projectId = $_POST['project_id'] ?? 0;
$periodType = $_POST['period_type'] ?? 'yearly';
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$format = $_POST['format'] ?? 'html';

// Validate inputs
if (empty($projectId)) {
    die("Необходимо выбрать проект для генерации отчета");
}

// Get project data
$project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$projectId]);
if (!$project) {
    die("Проект не найден");
}

// Perform analysis
$analysisResults = $analysis->calculateProjectAnalysis($projectId);

if (isset($analysisResults['error'])) {
    die($analysisResults['error']);
}

// Generate report based on format
switch ($format) {
    case 'pdf':
        // For PDF generation, we would use a library like TCPDF or FPDF
        // This is a placeholder implementation
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="report_' . $projectId . '_' . date('Y-m-d') . '.pdf"');
        echo "%PDF-1.4\n";
        echo "1 0 obj\n";
        echo "<< /Type /Catalog /Pages 2 0 R >>\n";
        echo "endobj\n";
        echo "2 0 obj\n";
        echo "<< /Type /Pages /Count 1 /Kids [3 0 R] >>\n";
        echo "endobj\n";
        echo "3 0 obj\n";
        echo "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >>\n";
        echo "endobj\n";
        echo "4 0 obj\n";
        echo "<< /Length 44 >>\n";
        echo "stream\n";
        echo "BT /F1 12 Tf 72 720 Td (Отчет по проекту: " . $project['name'] . ") Tj ET\n";
        echo "BT /F1 12 Tf 72 700 Td (ROI: " . number_format($analysisResults['roi'], 2) . "%) Tj ET\n";
        echo "BT /F1 12 Tf 72 680 Td (NPV: " . number_format($analysisResults['npv'], 2) . " руб.) Tj ET\n";
        echo "BT /F1 12 Tf 72 660 Td (IRR: " . number_format($analysisResults['irr'] * 100, 2) . "%) Tj ET\n";
        echo "BT /F1 12 Tf 72 640 Td (Срок окупаемости: " . ($analysisResults['payback_period'] > 0 ? number_format($analysisResults['payback_period'], 2) . " лет" : "Не окупается") . ") Tj ET\n";
        echo "endstream\n";
        echo "endobj\n";
        echo "xref\n";
        echo "0 5\n";
        echo "0000000000 65535 f \n";
        echo "0000000010 00000 n \n";
        echo "0000000053 00000 n \n";
        echo "0000000124 00000 n \n";
        echo "0000000210 00000 n \n";
        echo "trailer << /Size 5 /Root 1 0 R >>\n";
        echo "startxref\n";
        echo "309\n";
        echo "%%EOF\n";
        break;
        
    case 'excel':
        // For Excel generation, we would use a library like PhpSpreadsheet
        // This is a placeholder implementation
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="report_' . $projectId . '_' . date('Y-m-d') . '.xls"');
        
        echo "Отчет по проекту\t" . $project['name'] . "\n";
        echo "Показатель\tЗначение\n";
        echo "ROI (%)\t" . number_format($analysisResults['roi'], 2) . "\n";
        echo "NPV (руб.)\t" . number_format($analysisResults['npv'], 2, '.', '') . "\n";
        echo "IRR (%)\t" . number_format($analysisResults['irr'] * 100, 2) . "\n";
        echo "Срок окупаемости\t" . ($analysisResults['payback_period'] > 0 ? number_format($analysisResults['payback_period'], 2) . " лет" : "Не окупается") . "\n";
        echo "\n";
        echo "Прогнозные сценарии:\n";
        echo "Год\tОптимистичный\tБазовый\tПессимистичный\n";
        for ($i = 1; $i <= 3; $i++) {
            echo $i . "\t";
            echo (isset($analysisResults['forecast_scenarios']['optimistic'][$i-1]) ? number_format($analysisResults['forecast_scenarios']['optimistic'][$i-1], 2, '.', '') : 0) . "\t";
            echo (isset($analysisResults['forecast_scenarios']['base'][$i-1]) ? number_format($analysisResults['forecast_scenarios']['base'][$i-1], 2, '.', '') : 0) . "\t";
            echo (isset($analysisResults['forecast_scenarios']['pessimistic'][$i-1]) ? number_format($analysisResults['forecast_scenarios']['pessimistic'][$i-1], 2, '.', '') : 0) . "\t";
            echo "\n";
        }
        break;
        
    case 'html':
    default:
        // Generate HTML report
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Отчет по проекту <?php echo htmlspecialchars($project['name']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .summary { margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .footer { margin-top: 30px; text-align: center; font-size: 0.8em; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Отчет по проекту: <?php echo htmlspecialchars($project['name']); ?></h1>
                <p>Сформирован: <?php echo date('d.m.Y H:i'); ?></p>
            </div>
            
            <div class="summary">
                <h2>Финансовые показатели</h2>
                <table>
                    <tr>
                        <th>Показатель</th>
                        <th>Значение</th>
                    </tr>
                    <tr>
                        <td>ROI (Рентабельность инвестиций)</td>
                        <td><?php echo number_format($analysisResults['roi'], 2); ?>%</td>
                    </tr>
                    <tr>
                        <td>NPV (Чистая приведенная стоимость)</td>
                        <td><?php echo number_format($analysisResults['npv'], 2, '.', ' '); ?> руб.</td>
                    </tr>
                    <tr>
                        <td>IRR (Внутренняя норма доходности)</td>
                        <td><?php echo number_format($analysisResults['irr'] * 100, 2); ?>%</td>
                    </tr>
                    <tr>
                        <td>Срок окупаемости</td>
                        <td><?php 
                            if ($analysisResults['payback_period'] > 0) {
                                echo number_format($analysisResults['payback_period'], 2) . ' лет';
                            } else {
                                echo 'Не окупается';
                            }
                        ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="summary">
                <h2>Финансовое состояние проекта</h2>
                <table>
                    <tr>
                        <th>Показатель</th>
                        <th>Значение</th>
                    </tr>
                    <tr>
                        <td>Общая выручка</td>
                        <td><?php echo number_format($analysisResults['total_revenue'], 2, '.', ' '); ?> руб.</td>
                    </tr>
                    <tr>
                        <td>Общие затраты</td>
                        <td><?php echo number_format($analysisResults['total_costs'], 2, '.', ' '); ?> руб.</td>
                    </tr>
                    <tr>
                        <td>Общая прибыль</td>
                        <td><?php echo number_format($analysisResults['total_profit'], 2, '.', ' '); ?> руб.</td>
                    </tr>
                    <tr>
                        <td>Общие инвестиции</td>
                        <td><?php echo number_format($analysisResults['total_investment'], 2, '.', ' '); ?> руб.</td>
                    </tr>
                </table>
            </div>
            
            <div class="summary">
                <h2>Прогнозные сценарии (на 3 года)</h2>
                <table>
                    <tr>
                        <th>Год</th>
                        <th>Оптимистичный сценарий</th>
                        <th>Базовый сценарий</th>
                        <th>Пессимистичный сценарий</th>
                    </tr>
                    <?php
                    for ($i = 1; $i <= 3; $i++) {
                        echo "<tr>";
                        echo "<td>Год " . $i . "</td>";
                        echo "<td>" . number_format($analysisResults['forecast_scenarios']['optimistic'][$i-1] ?? 0, 2, '.', ' ') . " руб.</td>";
                        echo "<td>" . number_format($analysisResults['forecast_scenarios']['base'][$i-1] ?? 0, 2, '.', ' ') . " руб.</td>";
                        echo "<td>" . number_format($analysisResults['forecast_scenarios']['pessimistic'][$i-1] ?? 0, 2, '.', ' ') . " руб.</td>";
                        echo "</tr>";
                    }
                    ?>
                </table>
            </div>
            
            <div class="footer">
                <p>Сформировано автоматически системой анализа и прогнозирования рентабельности инвестиций</p>
            </div>
        </body>
        </html>
        <?php
        break;
}

?>
<?php
require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/calculations.php';

$db = new Database();
$analysis = new InvestmentAnalysis();

$action = $_GET['action'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1><?php echo APP_NAME; ?></h1>
        <nav>
            <ul>
                <li><a href="?action=dashboard">Панель управления</a></li>
                <li><a href="?action=projects">Проекты</a></li>
                <li><a href="?action=data-input">Ввод данных</a></li>
                <li><a href="?action=analysis">Анализ</a></li>
                <li><a href="?action=reports">Отчеты</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        switch($action) {
            case 'dashboard':
                include 'pages/dashboard.php';
                break;
            case 'projects':
                include 'pages/projects.php';
                break;
            case 'data-input':
                include 'pages/data_input.php';
                break;
            case 'analysis':
                include 'pages/analysis.php';
                break;
            case 'reports':
                include 'pages/reports.php';
                break;
            default:
                include 'pages/dashboard.php';
                break;
        }
        ?>
    </main>

    <footer>
        <p>&copy; 2025 Программная система анализа и прогнозирования рентабельности инвестиций трубопрокатного завода</p>
    </footer>
</body>
</html>
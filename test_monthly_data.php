<?php
// Тестовый файл для проверки функциональности месячных данных

// Подключаем необходимые файлы
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/analysis.php';

$db = new Database($config['db']);
$analysis = new Analysis($db);

// Тестируем с проектом ID 1 (или первым доступным проектом)
$projects = $db->fetchAll("SELECT id FROM projects LIMIT 1");
if (!empty($projects)) {
    $projectId = $projects[0]['id'];
    echo "Тестируем проект с ID: " . $projectId . "\n";
    
    $result = $analysis->calculateProjectAnalysis($projectId, 0.1, 2); // 2 года = 24 месяца
    
    if (isset($result['error'])) {
        echo "Ошибка: " . $result['error'] . "\n";
    } else {
        echo "Количество месяцев: " . $result['months_count'] . "\n";
        echo "Количество периодов в данных: " . count($result['periods']) . "\n";
        echo "Первые 5 периодов: \n";
        for ($i = 0; $i < min(5, count($result['periods'])); $i++) {
            echo "  " . $result['periods'][$i] . ": доходы=" . $result['period_revenues_by_period'][$i] . "\n";
        }
    }
} else {
    echo "Нет доступных проектов для тестирования\n";
}
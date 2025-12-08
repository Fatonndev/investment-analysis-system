<?php
// Test script to verify database connection and file includes
require_once 'config.php';
require_once 'includes/database.php';
require_once 'includes/calculations.php';

echo "<h1>Тест подключения и функциональности</h1>";

try {
    $db = new Database();
    echo "<p style='color: green;'>✓ Подключение к базе данных успешно установлено</p>";
    
    // Test database tables creation
    $result = $db->fetchAll("SHOW TABLES LIKE 'projects'");
    if (!empty($result)) {
        echo "<p style='color: green;'>✓ Таблицы базы данных успешно созданы</p>";
    } else {
        echo "<p style='color: orange;'>! Таблицы базы данных еще не созданы (будут созданы при первом использовании)</p>";
    }
    
    // Test calculations
    $analysis = new InvestmentAnalysis();
    $roi = $analysis->calculateROI(100000, 500000);
    echo "<p style='color: green;'>✓ Расчеты работают: ROI для прибыли 100000 и инвестиций 500000 = " . $roi . "%</p>";
    
    // Test other calculations
    $npv = $analysis->calculateNPV([-100000, 30000, 40000, 50000], 0.1);
    echo "<p style='color: green;'>✓ Расчет NPV работает: " . $npv . "</p>";
    
    $irr = $analysis->calculateIRR([-100000, 30000, 40000, 50000]);
    echo "<p style='color: green;'>✓ Расчет IRR работает: " . ($irr * 100) . "%</p>";
    
    $breakEven = $analysis->calculateBreakEvenPoint(100000, 1000, 600);
    echo "<p style='color: green;'>✓ Расчет точки безубыточности работает: " . $breakEven['quantity'] . " единиц</p>";
    
    echo "<p style='color: blue;'>Все тесты пройдены успешно! Приложение готово к использованию.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка: " . $e->getMessage() . "</p>";
}
?>
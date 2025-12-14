<?php
// Установим переменные GET для имитации запроса
$_GET['project_id'] = 1;
$_GET['discount_rate'] = 10;
$_GET['forecast_years'] = 3;

// Подключим файл и выполним его
require_once 'get_cashflow_data.php';
?>
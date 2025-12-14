<?php
require_once 'includes/database.php';

$db = new Database();

// Добавим тестовый проект
$projectId = $db->insert('projects', [
    'name' => 'Тестовый проект',
    'description' => 'Проект для тестирования денежных потоков'
]);

// Добавим тестовые производственные данные
$productionData = [
    [
        'project_id' => $projectId,
        'period' => '2023-01-01',
        'product_type' => 'Труба стальная',
        'quantity' => 1000.00,
        'unit' => 'тонн',
        'revenue' => 5000000.00,
        'variable_costs' => 3000000.00,
        'fixed_costs' => 500000.00
    ],
    [
        'project_id' => $projectId,
        'period' => '2023-02-01',
        'product_type' => 'Труба стальная',
        'quantity' => 1200.00,
        'unit' => 'тонн',
        'revenue' => 6000000.00,
        'variable_costs' => 3600000.00,
        'fixed_costs' => 500000.00
    ],
    [
        'project_id' => $projectId,
        'period' => '2023-03-01',
        'product_type' => 'Труба стальная',
        'quantity' => 1100.00,
        'unit' => 'тонн',
        'revenue' => 5500000.00,
        'variable_costs' => 3300000.00,
        'fixed_costs' => 500000.00
    ]
];

foreach ($productionData as $data) {
    $db->insert('production_data', $data);
}

// Добавим тестовые инвестиционные данные
$investmentData = [
    [
        'project_id' => $projectId,
        'investment_type' => 'Оборудование',
        'description' => 'Покупка производственного оборудования',
        'amount' => 10000000.00,
        'investment_date' => '2022-12-01'
    ],
    [
        'project_id' => $projectId,
        'investment_type' => 'Здания',
        'description' => 'Строительство производственного здания',
        'amount' => 5000000.00,
        'investment_date' => '2022-11-15'
    ],
    [
        'project_id' => $projectId,
        'investment_type' => 'Оборудование',
        'description' => 'Дополнительное оборудование',
        'amount' => 2000000.00,
        'investment_date' => '2023-01-15'
    ]
];

foreach ($investmentData as $data) {
    $db->insert('investment_data', $data);
}

echo "Тестовые данные успешно добавлены!\n";
echo "Project ID: $projectId\n";
?>
<?php
require_once 'includes/database.php';

// Create database connection
$db = new Database();

// Add a test project
$projectId = $db->insert('projects', [
    'name' => 'Test Project 1',
    'description' => 'Test project for IRR calculation'
]);

echo "Created project with ID: $projectId\n";

// Add some production data
$periods = [
    '2024-01-01', '2024-02-01', '2024-03-01', '2024-04-01', 
    '2024-05-01', '2024-06-01', '2024-07-01', '2024-08-01',
    '2024-09-01', '2024-10-01', '2024-11-01', '2024-12-01'
];

foreach ($periods as $period) {
    $revenue = rand(1000000, 1500000); // Random revenue between 1M and 1.5M
    $costs = rand(700000, 900000);     // Random costs between 700K and 900K
    
    $db->insert('production_data', [
        'project_id' => $projectId,
        'period' => $period,
        'product_type' => 'Steel Pipe',
        'quantity' => rand(1000, 2000),
        'unit' => 'tons',
        'revenue' => $revenue,
        'variable_costs' => $costs * 0.7,
        'fixed_costs' => $costs * 0.3
    ]);
}

// Add some investment data
$investmentDates = ['2024-01-01', '2024-04-01', '2024-07-01', '2024-10-01'];
foreach ($investmentDates as $date) {
    $db->insert('investment_data', [
        'project_id' => $projectId,
        'investment_type' => $date === '2024-01-01' ? 'Initial' : 'Technology',
        'description' => "Investment for period $date",
        'amount' => rand(5000000, 10000000), // 5M to 10M
        'investment_date' => $date
    ]);
}

echo "Test data inserted successfully!\n";
?>
<?php
// Миграция для объединения таблиц затрат
require_once 'config.php';
require_once 'includes/database.php';

try {
    $db = new Database();
    echo "Подключение к базе данных успешно!\n";
    
    // Проверяем, существуют ли старые таблицы
    $oldTables = ['raw_material_costs', 'energy_costs', 'logistics_costs', 'labor_costs', 'depreciation_costs'];
    $existingTables = [];
    
    foreach ($oldTables as $table) {
        $result = $db->fetchOne("SHOW TABLES LIKE '$table'");
        if ($result) {
            $existingTables[] = $table;
            echo "Таблица $table существует и будет перенесена\n";
        } else {
            echo "Таблица $table не найдена\n";
        }
    }
    
    if (empty($existingTables)) {
        echo "Старые таблицы не найдены. Миграция не требуется.\n";
        exit;
    }
    
    // Переносим данные из старых таблиц в новую
    foreach ($existingTables as $oldTable) {
        $costType = '';
        switch ($oldTable) {
            case 'raw_material_costs':
                $costType = 'raw_material';
                break;
            case 'energy_costs':
                $costType = 'energy';
                break;
            case 'logistics_costs':
                $costType = 'logistics';
                break;
            case 'labor_costs':
                $costType = 'labor';
                break;
            case 'depreciation_costs':
                $costType = 'depreciation';
                break;
        }
        
        if ($costType) {
            $sql = "INSERT INTO operational_costs 
                    (project_id, period, cost_type, cost, total_cost, material_type, cost_per_unit, quantity_used, 
                     energy_type, route, department, salary_cost, benefits, asset_name, depreciation_amount, created_at)
                    SELECT 
                        project_id, 
                        period, 
                        '$costType' as cost_type,
                        CASE 
                            WHEN '$costType' = 'logistics' THEN cost
                            ELSE NULL
                        END as cost,
                        CASE 
                            WHEN '$costType' IN ('raw_material', 'energy', 'labor') THEN total_cost
                            ELSE NULL
                        END as total_cost,
                        CASE 
                            WHEN '$costType' = 'raw_material' THEN material_type
                            ELSE NULL
                        END as material_type,
                        CASE 
                            WHEN '$costType' IN ('raw_material', 'energy') THEN cost_per_unit
                            ELSE NULL
                        END as cost_per_unit,
                        CASE 
                            WHEN '$costType' IN ('raw_material', 'energy') THEN quantity_used
                            ELSE NULL
                        END as quantity_used,
                        CASE 
                            WHEN '$costType' = 'energy' THEN energy_type
                            ELSE NULL
                        END as energy_type,
                        CASE 
                            WHEN '$costType' = 'logistics' THEN route
                            ELSE NULL
                        END as route,
                        CASE 
                            WHEN '$costType' = 'labor' THEN department
                            ELSE NULL
                        END as department,
                        CASE 
                            WHEN '$costType' = 'labor' THEN salary_cost
                            ELSE NULL
                        END as salary_cost,
                        CASE 
                            WHEN '$costType' = 'labor' THEN benefits
                            ELSE NULL
                        END as benefits,
                        CASE 
                            WHEN '$costType' = 'depreciation' THEN asset_name
                            ELSE NULL
                        END as asset_name,
                        CASE 
                            WHEN '$costType' = 'depreciation' THEN depreciation_amount
                            ELSE NULL
                        END as depreciation_amount,
                        created_at
                    FROM $oldTable";
            
            $db->getConnection()->query($sql);
            $affectedRows = $db->getConnection()->affected_rows;
            echo "Перенесено $affectedRows записей из таблицы $oldTable\n";
        }
    }
    
    // Проверяем, что данные перенесены
    $totalRecords = $db->fetchOne("SELECT COUNT(*) as count FROM operational_costs");
    echo "Всего записей в новой таблице operational_costs: " . $totalRecords['count'] . "\n";
    
    // Выводим статистику по типам затрат
    $stats = $db->fetchAll("SELECT cost_type, COUNT(*) as count FROM operational_costs GROUP BY cost_type");
    foreach ($stats as $stat) {
        echo "Тип затрат {$stat['cost_type']}: {$stat['count']} записей\n";
    }
    
    echo "Миграция данных завершена успешно!\n";
    echo "Теперь можно удалить старые таблицы вручную, если все работает корректно.\n";
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
?>
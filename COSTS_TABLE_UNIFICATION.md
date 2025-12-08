# Объединение таблиц затрат

## Описание изменений

Были объединены 5 отдельных таблиц затрат в одну общую таблицу `operational_costs`:

- `raw_material_costs` → тип `raw_material`
- `energy_costs` → тип `energy` 
- `logistics_costs` → тип `logistics`
- `labor_costs` → тип `labor`
- `depreciation_costs` → тип `depreciation`

## Структура новой таблицы

```sql
CREATE TABLE operational_costs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    period DATE,
    cost_type ENUM('raw_material', 'energy', 'logistics', 'labor', 'depreciation') NOT NULL,
    -- Common fields
    cost DECIMAL(15,2),
    total_cost DECIMAL(15,2),
    -- Raw material specific fields
    material_type VARCHAR(100),
    cost_per_unit DECIMAL(10,2),
    quantity_used DECIMAL(15,2),
    -- Energy specific fields
    energy_type VARCHAR(100),
    -- Logistics specific fields
    route VARCHAR(100),
    -- Labor specific fields
    department VARCHAR(100),
    salary_cost DECIMAL(15,2),
    benefits DECIMAL(15,2),
    -- Depreciation specific fields
    asset_name VARCHAR(100),
    depreciation_amount DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

## Измененные файлы

1. `includes/database.php` - добавлена новая таблица, удалены старые
2. `includes/calculations.php` - обновлен запрос для получения данных о затратах
3. `pages/data_input.php` - обновлены функции добавления, удаления и отображения затрат
4. `test_db_fix.php` - обновлены проверки таблиц
5. `migrate_costs_table.php` - скрипт для переноса данных из старых таблиц

## Преимущества объединения

1. **Упрощение структуры БД** - одна таблица вместо пяти
2. **Легче поддерживать** - одно место для добавления новых типов затрат
3. **Улучшенная гибкость** - можно легко добавлять новые типы затрат
4. **Упрощение запросов** - один JOIN вместо пяти
5. **Экономия ресурсов** - одна таблица использует меньше системных ресурсов

## Миграция

Для переноса существующих данных используйте скрипт `migrate_costs_table.php`
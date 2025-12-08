<div class="data-input-page">
    <h2>Ввод данных проекта</h2>
    
    <?php
    $projectId = $_GET['project_id'] ?? 0;
    
    if ($projectId == 0) {
        echo "<p>Пожалуйста, выберите проект для ввода данных.</p>";
        echo "<p><a href='?action=projects'>Выбрать проект</a></p>";
        return;
    }
    
    $project = $db->fetchOne("SELECT * FROM projects WHERE id = ?", [$projectId]);
    if (!$project) {
        echo "<p>Проект не найден.</p>";
        return;
    }
    
    echo "<h3>Проект: " . htmlspecialchars($project['name']) . "</h3>";
    
    // Get product types from database
    $productTypes = $db->fetchAll("SELECT * FROM product_types ORDER BY name");
    ?>
    
    <div class="data-input-tabs">
        <button class="tab-btn active" data-tab="production">Производственные данные</button>
        <button class="tab-btn" data-tab="costs">Затраты</button>
        <button class="tab-btn" data-tab="prices">Цены на продукцию</button>
        <button class="tab-btn" data-tab="investments">Инвестиции</button>
        <button class="tab-btn" data-tab="import">Импорт данных</button>
    </div>
    
    <div class="tab-content">
        <!-- Production Data Tab -->
        <div id="production-tab" class="tab-pane active">
            <h3>Производственные данные</h3>
            <form method="POST" action="?action=data-input&project_id=<?php echo $projectId; ?>">
                <input type="hidden" name="action" value="add_production">
                <div class="form-row">
                    <div class="form-group">
                        <label for="prod_period">Период (месяц):</label>
                        <input type="month" id="prod_period" name="period" required>
                    </div>
                    <div class="form-group">
                        <label for="product_type">Тип продукции:</label>
                        <select id="product_type" name="product_type" required>
                            <?php foreach ($productTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['name']); ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                            <?php if (empty($productTypes)): ?>
                                <option value="Стальные трубы">Стальные трубы</option>
                                <option value="Трубная заготовка">Трубная заготовка</option>
                                <option value="Оцинкованные трубы">Оцинкованные трубы</option>
                                <option value="Специальные трубы">Специальные трубы</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="quantity">Объем производства (тонны):</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="unit">Единица измерения:</label>
                        <select id="unit" name="unit" required>
                            <option value="тонны">тонны</option>
                            <option value="штуки">штуки</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="revenue">Выручка (руб.):</label>
                        <input type="number" id="revenue" name="revenue" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="variable_costs">Переменные затраты (руб.):</label>
                        <input type="number" id="variable_costs" name="variable_costs" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="fixed_costs">Постоянные затраты (руб.):</label>
                        <input type="number" id="fixed_costs" name="fixed_costs" step="0.01" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Добавить данные</button>
            </form>
            
            <!-- Display existing production data -->
            <h4>Существующие данные</h4>
            <?php
            $prodData = $db->fetchAll("SELECT * FROM production_data WHERE project_id = ? ORDER BY period DESC", [$projectId]);
            if (!empty($prodData)) {
                echo "<table class='data-table'>";
                echo "<thead><tr><th>Период</th><th>Тип продукции</th><th>Объем</th><th>Ед.изм.</th><th>Выручка</th><th>Перем.затраты</th><th>Пост.затраты</th><th>Действия</th></tr></thead>";
                echo "<tbody>";
                foreach ($prodData as $data) {
                    echo "<tr>";
                    echo "<td>" . $data['period'] . "</td>";
                    echo "<td>" . htmlspecialchars($data['product_type']) . "</td>";
                    echo "<td>" . $data['quantity'] . "</td>";
                    echo "<td>" . htmlspecialchars($data['unit']) . "</td>";
                    echo "<td>" . number_format($data['revenue'], 2, '.', ' ') . "</td>";
                    echo "<td>" . number_format($data['variable_costs'], 2, '.', ' ') . "</td>";
                    echo "<td>" . number_format($data['fixed_costs'], 2, '.', ' ') . "</td>";
                    echo "<td><a href='?action=data-input&project_id=$projectId&delete_prod=" . $data['id'] . "' class='btn-small btn-danger' onclick='return confirm(\"Удалить запись?\")'>Удалить</a></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Нет введенных производственных данных</p>";
            }
            ?>
        </div>
        
        <!-- Costs Tab -->
        <div id="costs-tab" class="tab-pane">
            <h3>Данные о затратах</h3>
            <form method="POST" action="?action=data-input&project_id=<?php echo $projectId; ?>">
                <input type="hidden" name="action" value="add_cost">
                <div class="form-row">
                    <div class="form-group">
                        <label for="cost_period">Период (месяц):</label>
                        <input type="month" id="cost_period" name="period" required>
                    </div>
                    <div class="form-group">
                        <label for="cost_type">Тип затрат:</label>
                        <select id="cost_type" name="cost_type" required onchange="toggleCostFields()">
                            <option value="raw_material">Сырье (сталь)</option>
                            <option value="energy">Энергоносители</option>
                            <option value="logistics">Логистика</option>
                            <option value="labor">Заработная плата</option>
                            <option value="depreciation">Амортизация</option>
                        </select>
                    </div>
                </div>
                
                <div id="raw-material-fields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="material_type">Тип материала:</label>
                            <select id="material_type" name="material_type">
                                <option value="Сталь">Сталь</option>
                                <option value="Трубы-заготовки">Трубы-заготовки</option>
                                <option value="Покрытия">Покрытия</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cost_per_unit_raw">Стоимость за единицу (руб.):</label>
                            <input type="number" id="cost_per_unit_raw" name="cost_per_unit" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="quantity_used_raw">Количество использовано:</label>
                            <input type="number" id="quantity_used_raw" name="quantity_used" step="0.01" required>
                        </div>
                    </div>
                </div>
                
                <div id="energy-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="energy_type">Тип энергии:</label>
                            <select id="energy_type" name="energy_type">
                                <option value="Электроэнергия">Электроэнергия</option>
                                <option value="Газ">Газ</option>
                                <option value="Пар">Пар</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cost_per_unit_energy">Стоимость за единицу (руб.):</label>
                            <input type="number" id="cost_per_unit_energy" name="cost_per_unit" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="quantity_used_energy">Количество использовано:</label>
                            <input type="number" id="quantity_used_energy" name="quantity_used" step="0.01">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Добавить затраты</button>
            </form>
        </div>
        
        <!-- Prices Tab -->
        <div id="prices-tab" class="tab-pane">
            <h3>Цены на готовую продукцию</h3>
            <form method="POST" action="?action=data-input&project_id=<?php echo $projectId; ?>">
                <input type="hidden" name="action" value="add_price">
                <div class="form-row">
                    <div class="form-group">
                        <label for="price_period">Период (месяц):</label>
                        <input type="month" id="price_period" name="period" required>
                    </div>
                    <div class="form-group">
                        <label for="price_product_type">Тип продукции:</label>
                        <select id="price_product_type" name="product_type" required>
                            <?php foreach ($productTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['name']); ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                            <?php endforeach; ?>
                            <?php if (empty($productTypes)): ?>
                                <option value="Стальные трубы">Стальные трубы</option>
                                <option value="Трубная заготовка">Трубная заготовка</option>
                                <option value="Оцинкованные трубы">Оцинкованные трубы</option>
                                <option value="Специальные трубы">Специальные трубы</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="size_spec">Типоразмер:</label>
                        <input type="text" id="size_spec" name="size_spec" placeholder="например: 108x4">
                    </div>
                    <div class="form-group">
                        <label for="precision_class">Класс точности:</label>
                        <select id="precision_class" name="precision_class">
                            <option value="обычный">Обычный</option>
                            <option value="повышенный">Повышенный</option>
                            <option value="высокий">Высокий</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="region">Регион сбыта:</label>
                        <input type="text" id="region" name="region" placeholder="например: Москва">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="price_per_unit">Цена за единицу (руб.):</label>
                    <input type="number" id="price_per_unit" name="price_per_unit" step="0.01" required>
                </div>
                
                <button type="submit" class="btn-primary">Добавить цену</button>
            </form>
        </div>
        
        <!-- Investments Tab -->
        <div id="investments-tab" class="tab-pane">
            <h3>Инвестиционные вложения</h3>
            <form method="POST" action="?action=data-input&project_id=<?php echo $projectId; ?>">
                <input type="hidden" name="action" value="add_investment">
                <div class="form-group">
                    <label for="investment_type">Тип инвестиции:</label>
                    <select id="investment_type" name="investment_type" required>
                        <option value="Оборудование">Оборудование</option>
                        <option value="Модернизация">Модернизация</option>
                        <option value="Строительство">Строительство</option>
                        <option value="Технологии">Технологии</option>
                        <option value="Прочее">Прочее</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="investment_description">Описание:</label>
                    <textarea id="investment_description" name="description" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="investment_amount">Сумма (руб.):</label>
                        <input type="number" id="investment_amount" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="investment_date">Дата вложения:</label>
                        <input type="date" id="investment_date" name="investment_date" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Добавить инвестицию</button>
            </form>
        </div>
        
        <!-- Import Data Tab -->
        <div id="import-tab" class="tab-pane">
            <h3>Импорт данных из Excel</h3>
            <form method="POST" enctype="multipart/form-data" action="?action=data-input&project_id=<?php echo $projectId; ?>">
                <input type="hidden" name="action" value="import_excel">
                <div class="form-group">
                    <label for="excel_file">Выберите Excel файл:</label>
                    <input type="file" id="excel_file" name="excel_file" accept=".xls,.xlsx" required>
                </div>
                <div class="form-group">
                    <label for="data_type">Тип данных для импорта:</label>
                    <select id="data_type" name="data_type" required>
                        <option value="production">Производственные данные</option>
                        <option value="costs">Затраты</option>
                        <option value="prices">Цены на продукцию</option>
                        <option value="investments">Инвестиции</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Импортировать</button>
            </form>
            <p class="help-text">Поддерживаются файлы Excel (.xls, .xlsx). Файл должен содержать правильные заголовки столбцов.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked button
            btn.classList.add('active');
            
            // Show corresponding pane
            const tabId = btn.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Cost type selection functionality
    function toggleCostFields() {
        const costType = document.getElementById('cost_type').value;
        document.getElementById('raw-material-fields').style.display = 'none';
        document.getElementById('energy-fields').style.display = 'none';
        
        if (costType === 'raw_material') {
            document.getElementById('raw-material-fields').style.display = 'block';
        } else if (costType === 'energy') {
            document.getElementById('energy-fields').style.display = 'block';
        }
    }
    
    // Set initial state
    toggleCostFields();
});

// Handle form submissions
<?php
if ($_POST['action'] ?? '' == 'add_production') {
    $period = $_POST['period'] . '-01'; // Convert month to first day of month
    $data = [
        'project_id' => $projectId,
        'period' => $period,
        'product_type' => $_POST['product_type'],
        'quantity' => $_POST['quantity'],
        'unit' => $_POST['unit'],
        'revenue' => $_POST['revenue'],
        'variable_costs' => $_POST['variable_costs'],
        'fixed_costs' => $_POST['fixed_costs']
    ];
    
    $db->insert('production_data', $data);
    echo "alert('Производственные данные успешно добавлены!');";
}

if (isset($_GET['delete_prod'])) {
    $prodId = (int)$_GET['delete_prod'];
    $db->executeQuery("DELETE FROM production_data WHERE id = ?", [$prodId]);
    echo "alert('Данные успешно удалены!');";
    echo "location.reload();";
}

if ($_POST['action'] ?? '' == 'add_cost') {
    $period = $_POST['period'] . '-01';
    $costType = $_POST['cost_type'];
    $totalCost = $_POST['cost_per_unit'] * $_POST['quantity_used'];
    
    switch ($costType) {
        case 'raw_material':
            $data = [
                'project_id' => $projectId,
                'period' => $period,
                'material_type' => $_POST['material_type'],
                'cost_per_unit' => $_POST['cost_per_unit'],
                'quantity_used' => $_POST['quantity_used'],
                'total_cost' => $totalCost
            ];
            $db->insert('raw_material_costs', $data);
            break;
            
        case 'energy':
            $data = [
                'project_id' => $projectId,
                'period' => $period,
                'energy_type' => $_POST['energy_type'],
                'cost_per_unit' => $_POST['cost_per_unit'],
                'quantity_used' => $_POST['quantity_used'],
                'total_cost' => $totalCost
            ];
            $db->insert('energy_costs', $data);
            break;
    }
    
    echo "alert('Затраты успешно добавлены!');";
}

if ($_POST['action'] ?? '' == 'add_price') {
    $period = $_POST['period'] . '-01';
    $data = [
        'project_id' => $projectId,
        'period' => $period,
        'product_type' => $_POST['product_type'],
        'size_spec' => $_POST['size_spec'],
        'precision_class' => $_POST['precision_class'],
        'region' => $_POST['region'],
        'price_per_unit' => $_POST['price_per_unit']
    ];
    
    $db->insert('product_prices', $data);
    echo "alert('Цена успешно добавлена!');";
}

if ($_POST['action'] ?? '' == 'add_investment') {
    $data = [
        'project_id' => $projectId,
        'investment_type' => $_POST['investment_type'],
        'description' => $_POST['description'],
        'amount' => $_POST['amount'],
        'investment_date' => $_POST['investment_date']
    ];
    
    $db->insert('investment_data', $data);
    echo "alert('Инвестиция успешно добавлена!');";
}

if ($_POST['action'] ?? '' == 'import_excel') {
    echo "alert('Функция импорта Excel временно недоступна в этой версии. Для полной реализации потребуется библиотека для работы с Excel файлами.');";
}
?>
</script>
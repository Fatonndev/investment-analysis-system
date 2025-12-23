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
    
    // Process form submissions
    // Ensure we only process POST requests for form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'add_production') {
            $period = $_POST['period'] . '-01'; // Convert month to first day of month
            $data = [
                'project_id' => $projectId,
                'period' => $period,
                'product_type' => $_POST['product_type'],
                'quantity' => (float)$_POST['quantity'],
                'unit' => $_POST['unit'],
                'revenue' => (float)$_POST['revenue'],
                'variable_costs' => (float)$_POST['variable_costs'],
                'fixed_costs' => (float)$_POST['fixed_costs']
            ];

            $db->insert('production_data', $data);
            // Redirect to prevent re-submission and stay on the production tab
            header("Location: ?action=data-input&project_id=$projectId&tab=production");
            exit();
        }
        elseif ($action === 'add_cost') {
            $period = $_POST['period'] . '-01';
            $costType = $_POST['cost_type'];

            switch ($costType) {
                case 'raw_material':
                    $quantityUsed = (float)($_POST['quantity_used'] ?? 0);
                    $costPerUnit = (float)($_POST['cost_per_unit'] ?? 0);
                    $totalCost = $quantityUsed * $costPerUnit;
                    $data = [
                        'project_id' => $projectId,
                        'period' => $period,
                        'cost_type' => 'raw_material',
                        'material_type' => $_POST['material_type'],
                        'cost_per_unit' => $costPerUnit,
                        'quantity_used' => $quantityUsed,
                        'total_cost' => $totalCost
                    ];
                    $db->insert('operational_costs', $data);
                    break;

                case 'energy':
                    $quantityUsed = (float)($_POST['quantity_used'] ?? 0);
                    $costPerUnit = (float)($_POST['cost_per_unit'] ?? 0);
                    $totalCost = $quantityUsed * $costPerUnit;
                    $data = [
                        'project_id' => $projectId,
                        'period' => $period,
                        'cost_type' => 'energy',
                        'energy_type' => $_POST['energy_type'],
                        'cost_per_unit' => $costPerUnit,
                        'quantity_used' => $quantityUsed,
                        'total_cost' => $totalCost
                    ];
                    $db->insert('operational_costs', $data);
                    break;

                case 'logistics':
                    $quantityUsed = (float)($_POST['quantity_used'] ?? 0);
                    $costPerUnit = (float)($_POST['cost_per_unit'] ?? 0);
                    $totalCost = $quantityUsed * $costPerUnit;
                    $data = [
                        'project_id' => $projectId,
                        'period' => $period,
                        'cost_type' => 'logistics',
                        'route' => $_POST['route'],
                        'cost_per_unit' => $costPerUnit,
                        'quantity_used' => $quantityUsed,
                        'total_cost' => $totalCost
                    ];
                    $db->insert('operational_costs', $data);
                    break;

                case 'labor':
                    $salaryCost = (float)($_POST['salary_cost'] ?? 0);
                    $benefits = (float)($_POST['benefits'] ?? 0);
                    $totalCost = $salaryCost + $benefits;
                    $data = [
                        'project_id' => $projectId,
                        'period' => $period,
                        'cost_type' => 'labor',
                        'department' => $_POST['department'],
                        'salary_cost' => $salaryCost,
                        'benefits' => $benefits,
                        'total_cost' => $totalCost
                    ];
                    $db->insert('operational_costs', $data);
                    break;

                case 'depreciation':
                    $depreciationAmount = (float)($_POST['depreciation_amount'] ?? 0);
                    $data = [
                        'project_id' => $projectId,
                        'period' => $period,
                        'cost_type' => 'depreciation',
                        'asset_name' => $_POST['asset_name'],
                        'depreciation_amount' => $depreciationAmount
                    ];
                    $db->insert('operational_costs', $data);
                    break;
            }

            // Redirect to prevent re-submission and stay on the costs tab
            header("Location: ?action=data-input&project_id=" . $projectId . "&tab=costs");
            exit();
        }
        elseif ($action === 'add_price') {
            $period = $_POST['period'] . '-01';
            $data = [
                'project_id' => $projectId,
                'period' => $period,
                'product_type' => $_POST['product_type'],
                'size_spec' => $_POST['size_spec'],
                'precision_class' => $_POST['precision_class'],
                'region' => $_POST['region'],
                'price_per_unit' => (float)$_POST['price_per_unit']
            ];

            $db->insert('product_prices', $data);
            // Redirect to prevent re-submission and stay on the prices tab
            header("Location: ?action=data-input&project_id=$projectId&tab=prices");
            exit();
        }
        elseif ($action === 'add_investment') {
            $data = [
                'project_id' => $projectId,
                'investment_type' => $_POST['investment_type'],
                'description' => $_POST['description'],
                'amount' => (float)$_POST['amount'],
                'investment_date' => $_POST['investment_date']
            ];

            $db->insert('investment_data', $data);
            // Redirect to prevent re-submission and stay on the investments tab
            header("Location: ?action=data-input&project_id=$projectId&tab=investments");
            exit();
        }
        elseif ($action === 'import_csv') {
            // Handle CSV file upload
            if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
                $uploadDir = dirname(__DIR__) . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = 'csv_import_' . time() . '_' . basename($_FILES['csv_file']['name']);
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $uploadPath)) {
                    // Process CSV import
                    require_once dirname(__DIR__) . '/includes/csv_handler.php';
                    $csvHandler = new CSVHandler($db);
                    $data_type = $_POST['data_type'];
                    $errors = [];
                    
                    switch ($data_type) {
                        case 'production':
                            $errors = $csvHandler->importProductionData($projectId, $uploadPath);
                            break;
                        case 'costs':
                            $errors = $csvHandler->importOperationalCosts($projectId, $uploadPath);
                            break;
                        case 'prices':
                            $errors = $csvHandler->importProductPrices($projectId, $uploadPath);
                            break;
                        case 'investments':
                            $errors = $csvHandler->importInvestmentData($projectId, $uploadPath);
                            break;
                        default:
                            $errors = ['Invalid data type specified'];
                    }
                    
                    // Remove the uploaded file after processing
                    unlink($uploadPath);
                    
                    // Redirect with success/error message
                    $tab = $data_type === 'investments' ? 'investments' : ($data_type === 'prices' ? 'prices' : ($data_type === 'costs' ? 'costs' : 'production'));
                    $message = empty($errors) ? 'CSV import completed successfully' : 'CSV import completed with errors: ' . implode(', ', $errors);
                    $messageType = empty($errors) ? 'success' : 'error';
                    
                    $redirectUrl = "?action=data-input&project_id=$projectId&tab=$tab&message=" . urlencode($message) . "&message_type=$messageType";
                    header("Location: $redirectUrl");
                    exit();
                } else {
                    $error = "Failed to upload file";
                    $redirectUrl = "?action=data-input&project_id=$projectId&tab=import&message=" . urlencode($error) . "&message_type=error";
                    header("Location: $redirectUrl");
                    exit();
                }
            } else {
                $error = "No file uploaded or upload error";
                $redirectUrl = "?action=data-input&project_id=$projectId&tab=import&message=" . urlencode($error) . "&message_type=error";
                header("Location: $redirectUrl");
                exit();
            }
        }
    }
    // Handle GET requests for deletion
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['delete_prod'])) {
            $prodId = (int)$_GET['delete_prod'];
            $db->executeQuery("DELETE FROM production_data WHERE id = ?", [$prodId]);
            // Redirect to remove the delete_prod parameter from URL to prevent repeated deletion and stay on the production tab
            header("Location: ?action=data-input&project_id=$projectId&tab=production");
            exit();
        }
        elseif (isset($_GET['delete_cost'])) {
            $costId = (int)$_GET['delete_cost'];
            $db->executeQuery("DELETE FROM operational_costs WHERE id = ?", [$costId]);
            // Redirect to remove the delete_cost parameter from URL to prevent repeated deletion and stay on the costs tab
            header("Location: ?action=data-input&project_id=$projectId&tab=costs");
            exit();
        }
        elseif (isset($_GET['delete_price'])) {
            $priceId = (int)$_GET['delete_price'];
            $db->executeQuery("DELETE FROM product_prices WHERE id = ?", [$priceId]);
            // Redirect to remove the delete_price parameter from URL to prevent repeated deletion and stay on the prices tab
            header("Location: ?action=data-input&project_id=$projectId&tab=prices");
            exit();
        }
        elseif (isset($_GET['delete_investment'])) {
            $investmentId = (int)$_GET['delete_investment'];
            $db->executeQuery("DELETE FROM investment_data WHERE id = ?", [$investmentId]);
            // Redirect to remove the delete_investment parameter from URL to prevent repeated deletion and stay on the investments tab
            header("Location: ?action=data-input&project_id=$projectId&tab=investments");
            exit();
        }
    }
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
                    echo "<td>" . htmlspecialchars($data['unit'] ?? '') . "</td>";
                    echo "<td>" . number_format($data['revenue'] ?? 0, 2, '.', ' ') . "</td>";
                    echo "<td>" . number_format($data['variable_costs'] ?? 0, 2, '.', ' ') . "</td>";
                    echo "<td>" . number_format($data['fixed_costs'] ?? 0, 2, '.', ' ') . "</td>";
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
                            <option value="">Выберите тип затрат</option>
                            <option value="raw_material">Сырье (сталь)</option>
                            <option value="energy">Энергоносители</option>
                            <option value="logistics">Логистика</option>
                            <option value="labor">Заработная плата</option>
                            <option value="depreciation">Амортизация</option>
                        </select>
                    </div>
                </div>
                
                <!-- Raw Material Fields -->
                <div id="raw-material-fields" class="cost-type-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="material_type">Тип материала:</label>
                            <select id="material_type" name="material_type">
                                <option value="Сталь">Сталь</option>
                                <option value="Трубы-заготовки">Трубы-заготовки</option>
                                <option value="Покрытия">Покрытия</option>
                                <option value="Прочее">Прочее</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity_used_raw">Количество использовано:</label>
                            <input type="number" id="quantity_used_raw" name="quantity_used" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="cost_per_unit_raw">Стоимость за единицу (руб.):</label>
                            <input type="number" id="cost_per_unit_raw" name="cost_per_unit" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <!-- Energy Fields -->
                <div id="energy-fields" class="cost-type-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="energy_type">Тип энергии:</label>
                            <select id="energy_type" name="energy_type">
                                <option value="Электроэнергия">Электроэнергия</option>
                                <option value="Газ">Газ</option>
                                <option value="Пар">Пар</option>
                                <option value="Нефть/нефтепродукты">Нефть/нефтепродукты</option>
                                <option value="Прочее">Прочее</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity_used_energy">Количество использовано:</label>
                            <input type="number" id="quantity_used_energy" name="quantity_used" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="cost_per_unit_energy">Стоимость за единицу (руб.):</label>
                            <input type="number" id="cost_per_unit_energy" name="cost_per_unit" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <!-- Logistics Fields -->
                <div id="logistics-fields" class="cost-type-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="logistics_route">Маршрут/Направление:</label>
                            <input type="text" id="logistics_route" name="route" placeholder="например: Москва-Санкт-Петербург">
                        </div>
                        <div class="form-group">
                            <label for="quantity_used_logistics">Количество использовано:</label>
                            <input type="number" id="quantity_used_logistics" name="quantity_used" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="cost_per_unit_logistics">Стоимость за единицу (руб.):</label>
                            <input type="number" id="cost_per_unit_logistics" name="cost_per_unit" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <!-- Labor Fields -->
                <div id="labor-fields" class="cost-type-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="labor_department">Отдел/Подразделение:</label>
                            <input type="text" id="labor_department" name="department" placeholder="например: Производство">
                        </div>
                        <div class="form-group">
                            <label for="salary_cost">ФОТ (руб.):</label>
                            <input type="number" id="salary_cost" name="salary_cost" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="benefits">Начисления (руб.):</label>
                            <input type="number" id="benefits" name="benefits" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <!-- Depreciation Fields -->
                <div id="depreciation-fields" class="cost-type-fields" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="asset_name">Наименование актива:</label>
                            <input type="text" id="asset_name" name="asset_name" placeholder="например: Стан прокатный">
                        </div>
                        <div class="form-group">
                            <label for="depreciation_amount">Сумма амортизации (руб.):</label>
                            <input type="number" id="depreciation_amount" name="depreciation_amount" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Добавить затраты</button>
            </form>
            
            <!-- Display existing costs data -->
            <h4>Существующие данные о затратах</h4>
            <?php
            $costsData = $db->fetchAll("SELECT * FROM operational_costs WHERE project_id = ? ORDER BY period DESC, cost_type", [$projectId]);

            if (!empty($costsData)) {
                echo "<table class='data-table'>";
                echo "<thead><tr><th>Период</th><th>Тип затрат</th><th>Детали</th><th>Стоимость</th><th>Доп.информация</th><th>Действия</th></tr></thead>";
                echo "<tbody>";

                foreach ($costsData as $cost) {
                    echo "<tr>";
                    echo "<td>" . date('Y-m', strtotime($cost['period'])) . "</td>";
                    
                    // Определяем тип затрат и отображаем соответствующую информацию
                    switch ($cost['cost_type']) {
                        case 'raw_material':
                            echo "<td>Сырье</td>";
                            echo "<td>" . htmlspecialchars($cost['material_type'] ?? '') . "</td>";
                            echo "<td>" . number_format($cost['total_cost'], 2, '.', ' ') . " руб.</td>";
                            echo "<td>Ед.стоимость: " . number_format($cost['cost_per_unit'], 2, '.', ' ') . " руб., Кол-во: " . $cost['quantity_used'] . "</td>";
                            break;
                        case 'energy':
                            echo "<td>Энергия</td>";
                            echo "<td>" . htmlspecialchars($cost['energy_type'] ?? '') . "</td>";
                            echo "<td>" . number_format($cost['total_cost'], 2, '.', ' ') . " руб.</td>";
                            echo "<td>Ед.стоимость: " . number_format($cost['cost_per_unit'], 2, '.', ' ') . " руб., Кол-во: " . $cost['quantity_used'] . "</td>";
                            break;
                        case 'logistics':
                            echo "<td>Логистика</td>";
                            echo "<td>" . htmlspecialchars($cost['route'] ?? '') . "</td>";
                            echo "<td>" . number_format($cost['total_cost'], 2, '.', ' ') . " руб.</td>";
                            echo "<td>Ед.стоимость: " . number_format($cost['cost_per_unit'], 2, '.', ' ') . " руб., Кол-во: " . $cost['quantity_used'] . "</td>";
                            break;
                        case 'labor':
                            echo "<td>Заработная плата</td>";
                            echo "<td>" . htmlspecialchars($cost['department'] ?? '') . "</td>";
                            echo "<td>" . number_format($cost['total_cost'], 2, '.', ' ') . " руб.</td>";
                            echo "<td>ФОТ: " . number_format($cost['salary_cost'], 2, '.', ' ') . " руб., Начисления: " . number_format($cost['benefits'], 2, '.', ' ') . " руб.</td>";
                            break;
                        case 'depreciation':
                            echo "<td>Амортизация</td>";
                            echo "<td>" . htmlspecialchars($cost['asset_name'] ?? '') . "</td>";
                            echo "<td>" . number_format($cost['depreciation_amount'], 2, '.', ' ') . " руб.</td>";
                            echo "<td>-</td>";
                            break;
                    }
                    
                    echo "<td><a href='?action=data-input&project_id=$projectId&delete_cost=" . $cost['id'] . "' class='btn-small btn-danger' onclick='return confirm(\"Удалить запись?\")'>Удалить</a></td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Нет введенных данных о затратах</p>";
            }
            ?>
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
            
            <!-- Display existing prices -->
            <h4>Существующие цены</h4>
            <?php
            $priceData = $db->fetchAll("SELECT * FROM product_prices WHERE project_id = ? ORDER BY period DESC", [$projectId]);
            if (!empty($priceData)) {
                echo "<table class='data-table'>";
                echo "<thead><tr><th>Период</th><th>Тип продукции</th><th>Типоразмер</th><th>Класс точности</th><th>Регион</th><th>Цена за единицу</th><th>Действия</th></tr></thead>";
                echo "<tbody>";
                foreach ($priceData as $data) {
                    echo "<tr>";
                    echo "<td>" . date('Y-m', strtotime($data['period'])) . "</td>";
                    echo "<td>" . htmlspecialchars($data['product_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($data['size_spec']) . "</td>";
                    echo "<td>" . htmlspecialchars($data['precision_class']) . "</td>";
                    echo "<td>" . htmlspecialchars($data['region']) . "</td>";
                    echo "<td>" . number_format($data['price_per_unit'], 2, '.', ' ') . "</td>";
                    echo "<td><a href='?action=data-input&project_id=$projectId&delete_price=" . $data['id'] . "' class='btn-small btn-danger' onclick='return confirm(\"Удалить запись?\")'>Удалить</a></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Нет введенных цен на продукцию</p>";
            }
            ?>
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
            
            <!-- Display existing investments data -->
            <h4>Существующие инвестиционные вложения</h4>
            <?php
            $investmentData = $db->fetchAll("SELECT * FROM investment_data WHERE project_id = ? ORDER BY investment_date DESC", [$projectId]);
            if (!empty($investmentData)) {
                echo "<table class='data-table'>";
                echo "<thead><tr><th>Тип инвестиции</th><th>Описание</th><th>Сумма</th><th>Дата вложения</th><th>Действия</th></tr></thead>";
                echo "<tbody>";
                foreach ($investmentData as $data) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($data['investment_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($data['description']) . "</td>";
                    echo "<td>" . number_format($data['amount'], 2, '.', ' ') . "</td>";
                    echo "<td>" . $data['investment_date'] . "</td>";
                    echo "<td><a href='?action=data-input&project_id=$projectId&delete_investment=" . $data['id'] . "' class='btn-small btn-danger' onclick='return confirm(\"Удалить запись?\")'>Удалить</a></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Нет введенных инвестиционных вложений</p>";
            }
            ?>
        </div>
        
        <!-- Import Data Tab -->
        <div id="import-tab" class="tab-pane">
            <h3>Импорт данных из CSV</h3>
            <?php
            // Display message if present
            if (isset($_GET['message'])) {
                $message = htmlspecialchars($_GET['message']);
                $messageType = $_GET['message_type'] ?? 'info';
                $alertClass = $messageType === 'error' ? 'alert-danger' : ($messageType === 'success' ? 'alert-success' : 'alert-info');
                echo "<div class='alert $alertClass'>$message</div>";
            }
            ?>
            <form method="POST" enctype="multipart/form-data" action="?action=data-input&project_id=<?php echo $projectId; ?>">
                <input type="hidden" name="action" value="import_csv">
                <div class="form-group">
                    <label for="csv_file">Выберите CSV файл:</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
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
            <p class="help-text">Поддерживаются файлы CSV (.csv). Файл должен содержать правильные заголовки столбцов.</p>
        </div>
    </div>
</div>

<script>
// Store current tab in session storage to preserve state across page reloads
function setCurrentTab(tabName) {
    sessionStorage.setItem('currentDataInputTab', tabName);
}

// Get current tab from session storage
function getCurrentTab() {
    return sessionStorage.getItem('currentDataInputTab');
}

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
            
            // Store the current tab in session storage
            const tabName = btn.getAttribute('data-tab');
            setCurrentTab(tabName);
        });
    });
    
    // Cost type selection functionality
    window.toggleCostFields = function() {
        const costType = document.getElementById("cost_type").value;

        // Hide all cost type fields and disable their inputs
        const allCostFields = document.querySelectorAll(".cost-type-fields");
        allCostFields.forEach(fieldGroup => {
            // Save required state for all inputs in this group before hiding
            const inputs = fieldGroup.querySelectorAll("input, select");
            inputs.forEach(input => {
                if(input.hasAttribute("required")) {
                    input.setAttribute("data-required", "required");
                    input.removeAttribute("required");
                }
            });
            
            // Hide the field group and disable inputs
            fieldGroup.style.display = "none";
            inputs.forEach(input => {
                input.disabled = true;
            });
        });

        // Show and enable fields for the selected cost type
        if (costType === "raw_material") {
            document.getElementById("raw-material-fields").style.display = "block";
            const rawMaterialInputs = document.getElementById("raw-material-fields").querySelectorAll("input, select");
            rawMaterialInputs.forEach(input => {
                input.disabled = false;
                // Restore required attribute if it was originally required
                if(input.hasAttribute("data-required")) {
                    input.setAttribute("required", "required");
                }
            });
        } else if (costType === "energy") {
            document.getElementById("energy-fields").style.display = "block";
            const energyInputs = document.getElementById("energy-fields").querySelectorAll("input, select");
            energyInputs.forEach(input => {
                input.disabled = false;
                // Restore required attribute if it was originally required
                if(input.hasAttribute("data-required")) {
                    input.setAttribute("required", "required");
                }
            });
        } else if (costType === "logistics") {
            document.getElementById("logistics-fields").style.display = "block";
            const logisticsInputs = document.getElementById("logistics-fields").querySelectorAll("input, select");
            logisticsInputs.forEach(input => {
                input.disabled = false;
                // Restore required attribute if it was originally required
                if(input.hasAttribute("data-required")) {
                    input.setAttribute("required", "required");
                }
            });
        } else if (costType === "labor") {
            document.getElementById("labor-fields").style.display = "block";
            const laborInputs = document.getElementById("labor-fields").querySelectorAll("input, select");
            laborInputs.forEach(input => {
                input.disabled = false;
                // Restore required attribute if it was originally required
                if(input.hasAttribute("data-required")) {
                    input.setAttribute("required", "required");
                }
            });
        } else if (costType === "depreciation") {
            document.getElementById("depreciation-fields").style.display = "block";
            const depreciationInputs = document.getElementById("depreciation-fields").querySelectorAll("input, select");
            depreciationInputs.forEach(input => {
                input.disabled = false;
                // Restore required attribute if it was originally required
                if(input.hasAttribute("data-required")) {
                    input.setAttribute("required", "required");
                }
            });
        }
    };

    // Set initial state
    toggleCostFields();
    
    // Check URL parameter to activate the correct tab (highest priority)
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab) {
        // Remove active class from all buttons and panes
        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));
        
        // Find and activate the requested tab
        const targetBtn = document.querySelector(`.tab-btn[data-tab="${activeTab}"]`);
        const targetPane = document.getElementById(`${activeTab}-tab`);
        
        if (targetBtn && targetPane) {
            targetBtn.classList.add('active');
            targetPane.classList.add('active');
            
            // Also store in session storage
            setCurrentTab(activeTab);
        }
    } else {
        // If no URL parameter, check session storage
        const storedTab = getCurrentTab();
        if (storedTab) {
            // Remove active class from all buttons and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Find and activate the stored tab
            const targetBtn = document.querySelector(`.tab-btn[data-tab="${storedTab}"]`);
            const targetPane = document.getElementById(`${storedTab}-tab`);
            
            if (targetBtn && targetPane) {
                targetBtn.classList.add('active');
                targetPane.classList.add('active');
            }
        }
    }
});
</script>

<div class="dashboard">
    <h2>Панель управления</h2>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Всего проектов</h3>
            <?php
            $projectCount = count($db->fetchAll("SELECT id FROM projects"));
            echo "<p class='stat-number'>$projectCount</p>";
            ?>
        </div>
        
        <div class="stat-card">
            <h3>Активные проекты</h3>
            <?php
            // For now, showing total projects as active
            echo "<p class='stat-number'>$projectCount</p>";
            ?>
        </div>
        
        <div class="stat-card">
            <h3>Средняя рентабельность</h3>
            <?php
            $discountRate = isset($_GET['discount_rate']) ? floatval($_GET['discount_rate']) : 0.1;
            $forecastYears = isset($_GET['forecast_years']) ? intval($_GET['forecast_years']) : 3;
            $avgProfitability = $analysis->calculateAverageProfitability($discountRate, $forecastYears);
            $avgProfitabilityFormatted = is_numeric($avgProfitability) ? round($avgProfitability, 2) : 0;
            echo "<p class='stat-number'>$avgProfitabilityFormatted%</p>";
            ?>
        </div>
        
        <div class="stat-card settings-card">
            <h3>Параметры расчета</h3>
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="action" value="dashboard">
                <label for="discount_rate">Ставка:</label>
                <input type="number" step="0.01" min="0" name="discount_rate" value="<?php echo $discountRate; ?>" style="width: 80px; padding: 3px;">
                <label for="forecast_years">Лет:</label>
                <input type="number" min="1" name="forecast_years" value="<?php echo $forecastYears; ?>" style="width: 60px; padding: 3px;">
                <button type="submit" style="padding: 3px 8px; font-size: 0.9em;">Обновить</button>
            </form>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="recent-projects">
            <h3>Последние проекты</h3>
            <?php
            $recentProjects = $db->fetchAll("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
            if (empty($recentProjects)) {
                echo "<p>Нет созданных проектов</p>";
            } else {
                echo "<ul>";
                foreach ($recentProjects as $project) {
                    echo "<li><a href='?action=analysis&project_id=" . $project['id'] . "'>" . htmlspecialchars($project['name']) . "</a></li>";
                }
                echo "</ul>";
            }
            ?>
        </div>
        
        <div class="quick-actions">
            <h3>Быстрые действия</h3>
            <ul>
                <li><a href="?action=projects">Управление проектами</a></li>
                <li><a href="?action=data-input">Ввод данных</a></li>
                <li><a href="?action=analysis">Анализ проектов</a></li>
                <li><a href="?action=reports">Генерация отчетов</a></li>
            </ul>
        </div>
    </div>
</div>
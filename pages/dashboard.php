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
            <p class='stat-number'>-</p>
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
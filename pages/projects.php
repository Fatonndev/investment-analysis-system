<div class="projects-page">
    <h2>Управление проектами</h2>
    
    <div class="projects-actions">
        <button id="new-project-btn" class="btn-primary">Создать новый проект</button>
    </div>
    
    <div id="new-project-form" style="display: none; margin: 20px 0;">
        <h3>Создать новый проект</h3>
        <form method="POST" action="?action=projects">
            <input type="hidden" name="action" value="create_project">
            <div class="form-group">
                <label for="project_name">Название проекта:</label>
                <input type="text" id="project_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="project_description">Описание проекта:</label>
                <textarea id="project_description" name="description"></textarea>
            </div>
            <button type="submit" class="btn-primary">Создать проект</button>
            <button type="button" id="cancel-project" class="btn-secondary">Отмена</button>
        </form>
    </div>
    
    <div class="projects-list">
        <h3>Список проектов</h3>
        <?php
        if ($_POST['action'] ?? '' == 'create_project') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if (!empty($name)) {
                $data = [
                    'name' => $name,
                    'description' => $description
                ];
                $db->insert('projects', $data);
                echo "<div class='alert-success'>Проект успешно создан!</div>";
            } else {
                echo "<div class='alert-error'>Ошибка: Название проекта обязательно!</div>";
            }
        }
        
        if (isset($_GET['delete_project'])) {
            $projectId = (int)$_GET['delete_project'];
            $db->executeQuery("DELETE FROM projects WHERE id = ?", [$projectId]);
            echo "<div class='alert-success'>Проект успешно удален!</div>";
        }
        
        $projects = $db->fetchAll("SELECT * FROM projects ORDER BY created_at DESC");
        
        if (empty($projects)) {
            echo "<p>Нет созданных проектов</p>";
        } else {
            echo "<table class='data-table'>";
            echo "<thead><tr><th>ID</th><th>Название</th><th>Описание</th><th>Дата создания</th><th>Действия</th></tr></thead>";
            echo "<tbody>";
            foreach ($projects as $project) {
                echo "<tr>";
                echo "<td>" . $project['id'] . "</td>";
                echo "<td>" . htmlspecialchars($project['name']) . "</td>";
                echo "<td>" . (htmlspecialchars(substr($project['description'], 0, 50)) ?: '-') . "...</td>";
                echo "<td>" . $project['created_at'] . "</td>";
                echo "<td>
                        <a href='?action=analysis&project_id=" . $project['id'] . "' class='btn-small btn-primary'>Анализ</a>
                        <a href='?action=data-input&project_id=" . $project['id'] . "' class='btn-small btn-secondary'>Данные</a>
                        <a href='?action=projects&delete_project=" . $project['id'] . "' class='btn-small btn-danger' onclick='return confirm(\"Удалить проект?\")'>Удалить</a>
                      </td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        }
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newProjectBtn = document.getElementById('new-project-btn');
    const newProjectForm = document.getElementById('new-project-form');
    const cancelBtn = document.getElementById('cancel-project');
    
    newProjectBtn.addEventListener('click', function() {
        newProjectForm.style.display = 'block';
    });
    
    cancelBtn.addEventListener('click', function() {
        newProjectForm.style.display = 'none';
    });
});
</script>
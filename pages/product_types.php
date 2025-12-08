<?php
require_once '../config.php';
require_once '../includes/database.php';

$db = new Database();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            if (!empty($name)) {
                $db->insert('product_types', [
                    'name' => $name,
                    'description' => $description
                ]);
                $message = "Тип продукции успешно добавлен";
            } else {
                $error = "Название типа продукции обязательно";
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = (int)$_POST['id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            
            if (!empty($name)) {
                $stmt = $db->getConnection()->prepare("UPDATE product_types SET name = ?, description = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $description, $id);
                $stmt->execute();
                $message = "Тип продукции успешно обновлен";
            } else {
                $error = "Название типа продукции обязательно";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $db->getConnection()->prepare("DELETE FROM product_types WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $message = "Тип продукции успешно удален";
        }
    }
}

// Handle edit form pre-fill
$editProductType = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editProductType = $db->fetchOne("SELECT * FROM product_types WHERE id = ?", [$editId]);
}
?>

<div class="product-types-page">
    <h2>Управление типами продукции</h2>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <!-- Form for adding/editing product types -->
    <div class="form-container">
        <h3><?php echo $editProductType ? 'Редактировать тип продукции' : 'Добавить новый тип продукции'; ?></h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="<?php echo $editProductType ? 'edit' : 'add'; ?>">
            <?php if ($editProductType): ?>
                <input type="hidden" name="id" value="<?php echo $editProductType['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Название типа продукции:</label>
                <input type="text" id="name" name="name" value="<?php echo $editProductType ? htmlspecialchars($editProductType['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description"><?php echo $editProductType ? htmlspecialchars($editProductType['description']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary"><?php echo $editProductType ? 'Обновить' : 'Добавить'; ?></button>
            <?php if ($editProductType): ?>
                <a href="?action=product-types" class="btn-secondary">Отмена</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- List of product types -->
    <div class="table-container">
        <h3>Существующие типы продукции</h3>
        <?php
        $productTypes = $db->fetchAll("SELECT * FROM product_types ORDER BY name");
        if (!empty($productTypes)):
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productTypes as $type): ?>
                <tr>
                    <td><?php echo $type['id']; ?></td>
                    <td><?php echo htmlspecialchars($type['name']); ?></td>
                    <td><?php echo htmlspecialchars($type['description']); ?></td>
                    <td><?php echo $type['created_at']; ?></td>
                    <td>
                        <a href="?action=product-types&edit=<?php echo $type['id']; ?>" class="btn-small btn-primary">Редактировать</a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить этот тип продукции?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $type['id']; ?>">
                            <button type="submit" class="btn-small btn-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Нет зарегистрированных типов продукции</p>
        <?php endif; ?>
    </div>
</div>
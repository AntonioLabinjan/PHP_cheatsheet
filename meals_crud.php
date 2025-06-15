<?php
$db = new PDO('sqlite:meals.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// CREATE TABLE
$db->exec("CREATE TABLE IF NOT EXISTS meals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    calories INTEGER NOT NULL
)");

// C
if (isset($_POST['add'])) {
    $stmt = $db->prepare("INSERT INTO meals (name, calories) VALUES (:name, :calories)");
    $stmt->execute([
        ':name' => $_POST['name'],
        ':calories' => $_POST['calories']
    ]);
    header("Location: index.php");
    exit;
}

// D
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM meals WHERE id = :id");
    $stmt->execute([':id' => $_GET['delete']]);
    header("Location: index.php");
    exit;
}

// U
if (isset($_POST['update'])) {
    $stmt = $db->prepare("UPDATE meals SET name = :name, calories = :calories WHERE id = :id");
    $stmt->execute([
        ':name' => $_POST['name'],
        ':calories' => $_POST['calories'],
        ':id' => $_POST['id']
    ]);
    header("Location: index.php");
    exit;
}

// R
$meals = $db->query("SELECT * FROM meals")->fetchAll(PDO::FETCH_ASSOC);

// R->E
$editMeal = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM meals WHERE id = :id");
    $stmt->execute([':id' => $_GET['edit']]);
    $editMeal = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Food Tracker</title>
    <style>
        body { font-family: Arial; margin: 40px; background: #f8f8f8; }
        h1 { color: #333; }
        form, table { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        input[type="text"], input[type="number"] {
            padding: 8px; width: 200px; margin-right: 10px; border: 1px solid #ccc; border-radius: 5px;
        }
        input[type="submit"] {
            padding: 8px 16px; border: none; background: #28a745; color: white; border-radius: 5px;
            cursor: pointer;
        }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        a { margin-right: 10px; text-decoration: none; color: #007bff; }
    </style>
</head>
<body>

<h1>🍽️ Food Tracker</h1>

<form method="POST">
    <?php if ($editMeal): ?>
        <input type="hidden" name="id" value="<?= $editMeal['id'] ?>">
        <input type="text" name="name" placeholder="Meal name" required value="<?= htmlspecialchars($editMeal['name']) ?>">
        <input type="number" name="calories" placeholder="Calories" required value="<?= $editMeal['calories'] ?>">
        <input type="submit" name="update" value="Update Meal">
    <?php else: ?>
        <input type="text" name="name" placeholder="Meal name" required>
        <input type="number" name="calories" placeholder="Calories" required>
        <input type="submit" name="add" value="Add Meal">
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <th>Meal</th>
            <th>Calories</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($meals as $meal): ?>
            <tr>
                <td><?= htmlspecialchars($meal['name']) ?></td>
                <td><?= $meal['calories'] ?></td>
                <td>
                    <a href="?edit=<?= $meal['id'] ?>">Edit</a>
                    <a href="?delete=<?= $meal['id'] ?>" onclick="return confirm('Delete this meal?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>

<?php
$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';


// view_pets.php
include 'db/db_pets.php'; // Подключаем файл с подключением к БД

// Выполняем SQL-запрос
$stmt = $pdo->query("SELECT * FROM db/db_pets"); // animals — имя вашей таблицы
$pets = $stmt->fetchAll(); // Получаем все строки результата
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список пропавших и найденных животных</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Пропавшие животные</h1>

    <?php if ($pets): ?>
        <ul>
            <?php foreach ($pets as $pet): ?>
                <li>
                    <strong><?= htmlspecialchars($pet['name']) ?></strong><br>
                    Порода: <?= htmlspecialchars($pet['breed']) ?><br>
                    Место пропажи: <?= htmlspecialchars($pet['location']) ?><br>
                    Дата пропажи: <?= htmlspecialchars($pet['date_lost']) ?>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Нет данных о пропавших животных.</p>
    <?php endif; ?>
</body>
</html>
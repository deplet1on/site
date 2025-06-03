<?php
// Подключаем файл с параметрами подключения к базе данных
require_once 'db/bd.php';

// Запрос к базе данных для получения всех строк из таблицы animals
$query = "SELECT * FROM found_pets";
$stmt = $pdo->query($query);

// Получаем результаты запроса в виде ассоциативного массива
$animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список животных</title>
</head>
<body>
    <h1>Список животных</h1>
    <table>
        <thead>
            <tr>
                <th>Порядкой номер в базе</th>
                <th>Имя</th>
                <th>Вид</th>
                <th>Описание</th>
                <th>Введите email</th>
                <th>Введите примерный адрес</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($animals)): ?>
                <?php foreach ($animals as $animal): ?>
                    <tr>
                        <td><?= htmlspecialchars($animal['id']) ?></td>
                        <td><?= htmlspecialchars($animal['name']) ?></td>
                        <td><?= htmlspecialchars($animal['species']) ?></td>
                        <td><?= htmlspecialchars($animal['description']) ?></td>
                        <td><?= htmlspecialchars($animal['email']) ?></td>
                        <td><?= htmlspecialchars($animal['approximate address']) ?></td>
                        <td><?= htmlspecialchars($animal['date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Нет данных о животных.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
</body>
</html>
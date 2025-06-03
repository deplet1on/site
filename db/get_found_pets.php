<?php
require_once 'bd.php';

// Установка кодировки для корректного отображения русских символов
header('Content-Type: application/json; charset=utf-8');

// Получаем данные
$stmt = $pdo->query("SELECT * FROM found_pets");
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка пути к фото
foreach ($pets as &$pet) {
    // Проверяем, если у животного есть фотография
    if (!empty($pet['photo'])) {
        if (strpos($pet['photo'], '/uploads') !== 0) {
            $pet['photo'] =$pet['photo'];
        }
    } else {
        $pet['photo'] = null;
    }
}
header('Content-Type: application/json');
echo json_encode($pets);
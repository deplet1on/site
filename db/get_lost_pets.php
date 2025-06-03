<?php
require_once 'bd.php';

// Установка кодировки для корректного отображения русских символов
header('Content-Type: application/json; charset=utf-8');

// Получаем данные из таблицы lost_animals
$stmt = $pdo->query("SELECT * FROM lost_animals");
$lost = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка пути к фото
foreach ($lost as &$pet) {
    if (!empty($pet['photo_path'])) {
        if (strpos($pet['photo_path'], '/uploads') !== 0) {
            $pet['photo_path'] = $pet['photo_path'];
        }
    } else {
        $pet['photo_path'] = null;
    }
}

echo json_encode($lost);
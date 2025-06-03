<?php
require_once 'db/bd.php';
$query = "SELECT * FROM rewiews";
$stmt = $pdo->query($query);

$animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Возвращаем данные в формате JSON
header('Content-Type: application/json');
return $animals;
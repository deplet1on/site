<?php
$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';

// Создание подключения
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Создание таблицы missing_pets, если она не существует
$sql = "CREATE TABLE IF NOT EXISTS missing_pets (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    species VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100) NOT NULL,
    date DATE NOT NULL
)";

$conn->query($sql);

// Создание таблицы found_pets, если она не существует
$sql = "CREATE TABLE IF NOT EXISTS found_pets (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    species VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    date DATE NOT NULL
)";

$conn->query($sql);
?>
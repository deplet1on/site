<?php
$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';
$conn = new mysqli("localhost", "username", "password", "database");

$stmt = $conn->prepare("INSERT INTO lost_animals (photo_path, address, owner_name, owner_email, description, animal_type, lost_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $photoPath, $address, $ownerName, $ownerEmail, $description, $animalType, $lostDate);

$photoPath = "/images/lost_cat1.jpg";
$address = "г. Омск, ул. Ленина, д. 10";
$ownerName = "Иван Петров";
$ownerEmail = "ivan@example.com";
$description = "Черная кошка с белыми лапами, около 1 года";
$animalType = "Кошка";
$lostDate = "2023-10-05";

$stmt->execute();
$stmt->close();
?>
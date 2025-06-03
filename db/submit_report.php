<?php
session_start();

$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функция для проверки reCAPTCHA
function verifyRecaptcha($response) {
    $secretKey = '6LcWgE4rAAAAAPwElVEmxKEuwUmxQFSW4MErmrvK'; // Замените на ваш секретный ключ
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    
    $data = [
        'secret' => $secretKey,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response_data = json_decode($result);
    
    return $response_data->success;
}

// Функция для проверки лимита запросов
function checkReportRateLimit($ip) {
    global $pdo;
    
    // Проверяем количество заявок от одного IP за последние 30 минут
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM missing_pets WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $stmt->execute([$ip]);
    $count = $stmt->fetchColumn();
    
    return $count < 5; // Максимум 5 заявок за 30 минут с одного IP
}

// Функция для очистки и валидации данных
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Функция для валидации email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Функция для валидации телефона
function validatePhone($phone) {
    // Простая проверка российского номера телефона
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return preg_match('/^(\+7|8)?[0-9]{10}$/', $phone);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Недействительный CSRF токен.');
    }
    
    // Проверка reCAPTCHA
    if (!isset($_POST['g-recaptcha-response']) || !verifyRecaptcha($_POST['g-recaptcha-response'])) {
        die('Проверка reCAPTCHA не пройдена.');
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Проверка лимита запросов
    if (!checkReportRateLimit($ip)) {
        die('Превышен лимит заявок. Попробуйте позже.');
    }
    
    // Валидация и очистка данных
    $pet_name = sanitizeInput($_POST['pet_name']);
    $pet_type = sanitizeInput($_POST['pet_type']);
    $description = sanitizeInput($_POST['description']);
    $contact_info = sanitizeInput($_POST['contact_info']);
    
    // Проверка длины данных
    if (strlen($pet_name) < 2 || strlen($pet_name) > 100) {
        die('Имя животного должно содержать от 2 до 100 символов.');
    }
    
    if (strlen($pet_type) < 2 || strlen($pet_type) > 50) {
        die('Тип животного должен содержать от 2 до 50 символов.');
    }
    
    if (strlen($description) < 10 || strlen($description) > 1000) {
        die('Описание должно содержать от 10 до 1000 символов.');
    }
    
    if (strlen($contact_info) < 5 || strlen($contact_info) > 200) {
        die('Контактная информация должна содержать от 5 до 200 символов.');
    }
    
    // Валидация контактной информации (должен быть email или телефон)
    if (!validateEmail($contact_info) && !validatePhone($contact_info)) {
        die('Некорректная контактная информация. Укажите действительный email или номер телефона.');
    }
    
    // Проверка на подозрительный контент
    $suspiciousWords = ['spam', 'casino', 'viagra', 'loan', 'credit', 'bitcoin', 'forex'];
    $contentToCheck = strtolower($pet_name . ' ' . $description);
    
    foreach ($suspiciousWords as $word) {
        if (strpos($contentToCheck, $word) !== false) {
            die('Заявка содержит недопустимый контент.');
        }
    }
    
    // Проверка на дублирование (похожие заявки от того же IP за последние 24 часа)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM missing_pets WHERE ip_address = ? AND pet_name = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute([$ip, $pet_name]);
    
    if ($stmt->fetchColumn() > 0) {
        die('Похожая заявка уже была отправлена.');
    }
    
    try {
        // Добавляем поля для IP адреса и времени создания
        $stmt = $pdo->prepare("INSERT INTO missing_pets (pet_name, pet_type, description, contact_info, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$pet_name, $pet_type, $description, $contact_info, $ip]);
        
        header("Location: index.php?message=success");
        exit;
    } catch (PDOException $e) {
        error_log("Database error in submit_report.php: " . $e->getMessage());
        die('Произошла ошибка при сохранении заявки.');
    }
} else {
    die('Неверный метод запроса.');
}
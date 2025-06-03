<?php
session_start();
require_once 'bd.php';

// Проверка CSRF токена
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token mismatch');
}

// Проверка reCAPTCHA v3
function verifyRecaptcha($token) {
    $secret = '6LcWgE4rAAAAAPwElVEmxKEuwUmxQFSW4MErmrvK'; // Ваш секретный ключ
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    
    $data = [
        'secret' => $secret,
        'response' => $token,
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
    
    if ($result === FALSE) {
        return false;
    }
    
    $response = json_decode($result, true);
    
    // Проверяем успешность и минимальный скор (для v3)
    return $response['success'] === true && 
           isset($response['score']) && 
           $response['score'] >= 0.5 && // Минимальный скор для прохождения
           isset($response['action']) && 
           $response['action'] === 'submit_review';
}

// Проверяем наличие reCAPTCHA токена
if (!isset($_POST['recaptcha_token']) || empty($_POST['recaptcha_token'])) {
    die('reCAPTCHA token отсутствует');
}

// Верифицируем reCAPTCHA
if (!verifyRecaptcha($_POST['recaptcha_token'])) {
    die('Проверка reCAPTCHA не пройдена. Попробуйте еще раз.');
}

// Валидация данных
$name = trim($_POST['name'] ?? '');
$review_text = trim($_POST['review'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);

// Проверки
if (strlen($name) < 2 || strlen($name) > 50) {
    die('Имя должно содержать от 2 до 50 символов');
}

if (strlen($review_text) < 10 || strlen($review_text) > 1000) {
    die('Отзыв должен содержать от 10 до 1000 символов');
}

if ($rating < 1 || $rating > 5) {
    die('Неверная оценка');
}

// Обработка загрузки фото
$photo_name = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = $_FILES['photo']['type'];
    $file_size = $_FILES['photo']['size'];
    
    // Проверка типа файла
    if (!in_array($file_type, $allowed_types)) {
        die('Неразрешенный тип файла');
    }
    
    // Проверка размера файла (10 МБ)
    if ($file_size > 10 * 1024 * 1024) {
        die('Размер файла превышает 10 МБ');
    }
    
    // Генерация уникального имени файла
    $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photo_name = uniqid() . '.' . $file_extension;
    $upload_path = '../uploads/' . $photo_name;
    
    // Создание директории uploads если её нет
    if (!is_dir('../uploads/')) {
        mkdir('../uploads/', 0755, true);
    }
    
    // Перемещение файла
    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
        die('Ошибка загрузки файла');
    }
}

try {
    // Подготовка SQL запроса
    $sql = "INSERT INTO reviews (name, review, rating, photo, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    // Выполнение запроса
    $stmt->execute([
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($review_text, ENT_QUOTES, 'UTF-8'),
        $rating,
        $photo_name
    ]);
    
    // Перенаправление с сообщением об успехе
    header('Location: ../index.php?review_success=1');
    exit();
    
} catch (PDOException $e) {
    // Удаляем загруженный файл в случае ошибки БД
    if ($photo_name && file_exists('../uploads/' . $photo_name)) {
        unlink('../uploads/' . $photo_name);
    }
    
    error_log("Ошибка добавления отзыва: " . $e->getMessage());
    die('Ошибка сохранения отзыва. Попробуйте позже.');
}
?>
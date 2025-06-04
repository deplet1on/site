<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Функция для логирования подозрительной активности
function logSuspiciousActivity($ip, $data, $reason = '') {
    $log = date('Y-m-d H:i:s') . " IP: $ip Reason: $reason Data: " . json_encode($data) . "\n";
    file_put_contents('security.log', $log, FILE_APPEND | LOCK_EX);
}

// Функция проверки rate limiting
function checkRateLimit() {
    session_start();
    $current_time = time();
    $time_limit = 300; // 5 минут
    $max_attempts = 3;

    if (!isset($_SESSION['form_attempts'])) {
        $_SESSION['form_attempts'] = [];
    }

    // Очищаем старые попытки
    $_SESSION['form_attempts'] = array_filter($_SESSION['form_attempts'], 
        function($time) use ($current_time, $time_limit) {
            return ($current_time - $time) < $time_limit;
        });

    if (count($_SESSION['form_attempts']) >= $max_attempts) {
        logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'Rate limit exceeded');
        die("<div class='alert alert-danger text-center'>Слишком много попыток. Попробуйте через 5 минут.</div>");
    }

    $_SESSION['form_attempts'][] = $current_time;
}

// Функция проверки reCAPTCHA v3
function verifyRecaptchaV3($recaptcha_response) {
    $recaptcha_secret = "6LcWgE4rAAAAAPwElVEmxKEuwUmxQFSW4MErmrvK";
    
    $post_data = http_build_query([
        'secret' => $recaptcha_secret,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => $post_data
        ]
    ]);
    
    $verify = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $captcha_success = json_decode($verify, true);
    
    // Для reCAPTCHA v3 проверяем success и score
    return $captcha_success['success'] && $captcha_success['score'] >= 0.5;
}

// Функция проверки на спам
function checkSpam($text) {
    $spam_keywords = [
        'http://', 'https://', 'www.', '.com', '.ru', '.org', '.net',
        'casino', 'viagra', 'cialis', 'poker', 'loan', 'credit',
        'bitcoin', 'crypto', 'investment', 'earnings', 'profit',
        'скидка', 'акция', 'заработок', 'кредит', 'займ'
    ];
    
    $text_lower = mb_strtolower($text);
    foreach ($spam_keywords as $keyword) {
        if (stripos($text_lower, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Список заблокированных IP (добавляйте сюда IP спамеров)
$blocked_ips = []; // Например: ['192.168.1.1', '10.0.0.1']

if (in_array($_SERVER['REMOTE_ADDR'], $blocked_ips)) {
    http_response_code(403);
    die("<div class='alert alert-danger text-center'>Доступ запрещен</div>");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Проверка rate limiting
    checkRateLimit();
    
    // Проверка согласия на обработку персональных данных
    if (!isset($_POST['privacy'])) {
        die("<div class='alert alert-danger text-center'>Необходимо согласиться на обработку персональных данных</div>");
    }
    
    // Проверка reCAPTCHA v3
    if (!isset($_POST['g-recaptcha-response']) || !verifyRecaptchaV3($_POST['g-recaptcha-response'])) {
        logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'reCAPTCHA v3 failed');
        die("<div class='alert alert-danger text-center'>Не удалось подтвердить, что вы человек. Попробуйте еще раз.</div>");
    }

    $host = 'localhost';
    $dbname = 'a1120713_11';
    $username = 'a1120713_11';
    $password = 'tumupiguzi';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Получаем и валидируем данные из формы
        $species = htmlspecialchars(trim($_POST['species'] ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = trim($_POST['email'] ?? '');
        $approximate_address = htmlspecialchars(trim($_POST['approximate_address'] ?? ''), ENT_QUOTES, 'UTF-8');
        $date = $_POST['date'] ?? date("Y-m-d");

        // Валидация длины полей
        if (strlen($description) > 1000) {
            logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'Description too long');
            die("<div class='alert alert-danger text-center'>Описание слишком длинное (максимум 1000 символов)</div>");
        }
        
        if (strlen($species) > 50) {
            logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'Species too long');
            die("<div class='alert alert-danger text-center'>Вид животного слишком длинный (максимум 50 символов)</div>");
        }

        // Проверка email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'Invalid email');
            die("<div class='alert alert-danger text-center'>Некорректный email адрес</div>");
        }

        // Проверка на спам
        if (checkSpam($description) || checkSpam($species) || checkSpam($approximate_address)) {
            logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'Spam detected');
            die("<div class='alert alert-danger text-center'>Обнаружен подозрительный контент</div>");
        }

        // Дополнительные проверки
        if (empty($species) || empty($email) || empty($approximate_address)) {
            die("<div class='alert alert-danger text-center'>Заполните все обязательные поля</div>");
        }

        // Обработка фото
        $photoPath = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Проверка размера файла (максимум 10MB)
            if ($_FILES['photo']['size'] > 10 * 1024 * 1024) {
                die("<div class='alert alert-danger text-center'>Файл слишком большой (максимум 10MB)</div>");
            }
            
            // Проверка типа файла
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($_FILES['photo']['type'], $allowed_types)) {
                die("<div class='alert alert-danger text-center'>Недопустимый тип файла. Разрешены только изображения.</div>");
            }
            
            $photoName = basename($_FILES['photo']['name']);
            $photoPath = $uploadDir . time() . '_' . $photoName;
            move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
        }

        // Получаем координаты (если есть)
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;

        // Вставка в БД
        $stmt = $pdo->prepare("INSERT INTO found_pets (species, description, email, `approximate address`, date, photo, latitude, longitude) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$species, $description, $email, $approximate_address, $date, $photoPath, $latitude, $longitude]);

        // Отправка письма на email пользователя
        $subject = "Ваш запрос о найденном животном";
        $message = "Ваше сообщение о найденном животном было успешно отправлено!\n\n";
        $message .= "Информация о животном:\n";
        $message .= "Вид: $species\nОписание: $description\nАдрес: $approximate_address\nДата: $date\n";
        $message .= "Фото: " . ($photoPath ? $photoPath : "Нет фото") . "\n\n";
        $message .= "Спасибо, что помогаете в поисках животных!";
        $headers = "From: no-reply@tailsearch.ru";

        // Отправка письма
        if (mail($email, $subject, $message, $headers)) {
            echo "<div class='alert alert-success text-center'>Информация о найденном животном успешно добавлена и письмо отправлено на ваш email!</div>";
        } else {
            echo "<div class='alert alert-warning text-center'>Не удалось отправить письмо на ваш email.</div>";
        }

    } catch (PDOException $e) {
        logSuspiciousActivity($_SERVER['REMOTE_ADDR'], $_POST, 'Database error: ' . $e->getMessage());
        echo "<div class='alert alert-danger text-center'>Ошибка при сохранении данных. Попробуйте позже.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить найденное животное</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Custom -->
    <link rel="stylesheet" href="styles.css">
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LcWgE4rAAAAAMIyY6LngeWTDsCVqSFhJ7q0hvHl"></script>
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=a97f57d3-dc31-4396-ac2d-78d71b4aced3" type="text/javascript"></script>

   <style>
        /* НОВЫЙ СТИЛЬ ДЛЯ КАРТЫ */
        #map-container {
            position: relative;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 соотношение */
            margin-bottom: 20px;
            overflow: hidden;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        /* Остальные стили остаются без изменений */
        .section-bg {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 20px;
        }
        
        /* ИСПРАВЛЕННЫЕ стили для переключателя согласия */
        .privacy-toggle {
            display: flex;
            align-items: center;
            margin: 15px 0;
            gap: 15px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
            flex-shrink: 0;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #29B6F6; /* Зеленый цвет при активации */
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .toggle-label {
            flex: 1;
            font-size: clamp(0.9rem, 2vw, 1rem);
            line-height: 1.4;
        }
        
        .toggle-label a {
            color: #007bff;
            text-decoration: none;
        }
        
        .toggle-label a:hover {
            text-decoration: underline;
        }
        
        /* Адаптивные исправления */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding: 10px 15px;
            position: relative;
        }
        
        .logo {
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            font-weight: bold;
            color: white;
            padding-right: 40px;
        }
        
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 20px;
            z-index: 1000;
        }
        
        .main-nav {
            display: flex;
            transition: max-height 0.3s ease;
        }
        
        .main-nav ul {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .main-nav li {
            margin: 5px 0;
        }
        
        .main-nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
            display: block;
            white-space: normal;
        }
        
        .main-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .steps-container, .form-container {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* Адаптация для планшетов */
        @media (max-width: 768px) {
            .privacy-toggle {
                align-items: flex-start;
                gap: 12px;
            }
            
            .toggle-switch {
                width: 50px;
                height: 28px;
                margin-top: 2px;
            }
            
            .slider:before {
                height: 22px;
                width: 22px;
                left: 3px;
                bottom: 3px;
            }
            
            input:checked + .slider:before {
                transform: translateX(22px);
            }
        }
        
        /* Адаптация для мобильных телефонов */
        @media (max-width: 480px) {
            .privacy-toggle {
                align-items: flex-start;
                gap: 10px;
            }
            
            .toggle-switch {
                width: 45px;
                height: 26px;
                margin-top: 2px;
            }
            
            .slider:before {
                height: 20px;
                width: 20px;
                left: 3px;
                bottom: 3px;
            }
            
            input:checked + .slider:before {
                transform: translateX(19px);
            }
            
            .toggle-label {
                font-size: 0.9rem;
            }
        }
        
        /* Для очень маленьких экранов */
        @media (max-width: 360px) {
            .toggle-switch {
                width: 40px;
                height: 24px;
            }
            
            .slider:before {
                height: 18px;
                width: 18px;
                left: 3px;
                bottom: 3px;
            }
            
            input:checked + .slider:before {
                transform: translateX(16px);
            }
        }
        
        @media (max-width: 992px) {
            .header-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .nav-toggle {
                display: block;
            }
            
            .main-nav {
                max-height: 0;
                overflow: hidden;
                width: 100%;
            }
            
            .main-nav.active {
                max-height: 500px;
            }
            
            .main-nav ul {
                flex-direction: column;
                padding: 10px 0;
            }
            
            .step {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .step-number {
                margin-bottom: 10px;
            }
        }
</style>
<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();
   for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
   k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(102377866, "init", {
        clickmap:true,
        trackLinks:true,
        accurateTrackBounce:true
   });
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/102377866" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
</noscript>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-SFL7Y734H5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-SFL7Y734H5');
</script>
</head>
<body>
<header>
    <div class="header-container">
        <div class="logo">Поиск хвостиков</div>
        <button class="nav-toggle" aria-label="Меню">☰</button>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="add_pet.php">Добавить пропавшее животное</a></li>
                <li><a href="view_pets.php">Список пропавших и найденных животных</a></li>
                <li><a href="found_pet.php">Сообщить о найденном животном</a></li>
                <li><a href="shelter.php">Приюты</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="container section-bg">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="steps-container">
                <h2>Два простых шага</h2> <!-- Изменено с 3 на 2 -->
                <div class="step d-flex">
                    <div class="step-number d-flex align-items-center justify-content-center">1</div>
                    <div class="step-content">
                        <h3>Опубликуйте объявление</h3>
                        <p>Разместите объявление о пропаже/находке питомца абсолютно бесплатно.</p>
                    </div>
                </div>
                <!-- УДАЛЕН ВТОРОЙ ПУНКТ -->
                <div class="step d-flex">
                    <div class="step-number d-flex align-items-center justify-content-center">2</div> <!-- Изменено с 3 на 2 -->
                    <div class="step-content">
                        <h3>Питомец дома</h3>
                        <p>С помощью нашей системы нашлось уже 2 314 питомцев...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="form-container">
                <h2>Добавить найденное животное</h2>
                <form method="POST" enctype="multipart/form-data" id="foundPetForm">
                    <div class="form-group mb-3">
                        <label for="photo">Прикрепить фотографию (до 10 мб):</label>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group mb-3">
                        <label for="species">Вид:</label>
                        <select id="species" name="species" class="form-control" required>
                            <option value="">-- Выберите --</option>
                            <option value="Собака">Собака</option>
                            <option value="Кошка">Кошка</option>
                            <option value="Попугай">Попугай</option>
                            <option value="Хомяк">Хомяк</option>
                            <option value="Кролик">Кролик</option>
                            <option value="Черепаха">Черепаха</option>
                            <option value="Другое">Другое</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label for="description">Описание:</label>
                        <textarea id="description" name="description" class="form-control" maxlength="1000" placeholder="Опишите животное (максимум 1000 символов)"></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Введите email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="date">Дата:</label>
                        <input type="date" id="date" name="date" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="approximate_address">Примерный адрес:</label>
                        <input type="text" id="approximate_address" name="approximate_address" class="form-control" required>
                    </div>

                    <div id="map-container">
                        <div id="map"></div>
                    </div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <!-- Согласие на обработку персональных данных -->
                <div class="privacy-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="privacy" id="privacy" required>
                        <span class="slider round"></span>
                    </label>
                    <label for="privacy" class="toggle-label">
                        <a href="/fz.php" target="_blank">Я согласен(на) на обработку персональных данных</a>
                    </label>
                </div>

                    <!-- Скрытое поле для reCAPTCHA v3 токена -->
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            
                    <button type="submit" class="btn btn-primary w-100">Добавить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <p>📞 +7 (995) 273-74-28</p>
        <p>✉️ vet.help@example.com</p>
        <p>🕒 Работаем: Круглосуточно</p>
        <p>&copy; <?= date('Y') ?> Поиск хвостиков. Все права защищены.</p>
    </div>
</footer>

<!-- Scripts -->
<script>
function reverseGeocode(lat, lon) {
    const url = `https://nominatim.openstreetmap.org/reverse.php?lat=${lat}&lon=${lon}&format=json`;

    return fetch(url, {
        method: 'GET',
        headers: {
            'User-Agent': 'MyApp/1.0 (myemail@example.com)'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        return data;
    })
    .catch(error => {
        console.error('Ошибка:', error);
        throw error;
    });
}

ymaps.ready(function () {
    const map = new ymaps.Map("map", {
        center: [54.9876, 73.3972],  // Центр карты (например, Омск)
        zoom: 12,
        controls: ["zoomControl", "fullscreenControl"]
    });

    let placemark;

    map.events.add('click', function (e) {
        const coords = e.get('coords');

        if (!placemark) {
            placemark = new ymaps.Placemark(coords, {}, {
                preset: 'islands#greenIcon'
            });
            map.geoObjects.add(placemark);
        } else {
            placemark.geometry.setCoordinates(coords);
        }

        document.getElementById('latitude').value = coords[0];
        document.getElementById('longitude').value = coords[1];
        reverseGeocode(coords[0], coords[1])
            .then(data => {
                const addressInput = document.getElementById('approximate_address');
                if (addressInput) {
                    addressInput.value = data.display_name || 'Адрес не найден';
                }
            })
            .catch(err => {
                console.error('Ошибка получения адреса:', err);
            });
    });
});

// reCAPTCHA v3 обработка
grecaptcha.ready(function() {
    document.getElementById('foundPetForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        grecaptcha.execute('6LcWgE4rAAAAAMIyY6LngeWTDsCVqSFhJ7q0hvHl', {action: 'submit'}).then(function(token) {
            document.getElementById('g-recaptcha-response').value = token;
            document.getElementById('foundPetForm').submit();
        });
    });
});

// Мобильное меню
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Функция для форматирования имени
    function formatName(input) {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                let words = this.value.trim().split(/\s+/);
                let formatted = words.map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                ).join(' ');
                this.value = formatted;
            }
        });
    }

    // Применяем к полям в разных формах
    const nameFields = [
        document.getElementById('reviewer-name'),
        document.getElementById('owner_name')
    ];
    
    nameFields.forEach(field => {
        if (field) formatName(field);
    });
});
</script>
</body>
</html>
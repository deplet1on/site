<?php
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

// Функция для проверки лимита запросов
function checkSearchRateLimit($ip) {
    // Простая проверка: максимум 30 поисковых запросов в минуту с одного IP
    $rateLimitFile = sys_get_temp_dir() . '/search_rate_' . md5($ip);
    $currentTime = time();
    
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true);
        
        // Очищаем старые записи (старше минуты)
        $data = array_filter($data, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 60;
        });
        
        if (count($data) >= 30) {
            return false;
        }
        
        $data[] = $currentTime;
    } else {
        $data = [$currentTime];
    }
    
    file_put_contents($rateLimitFile, json_encode($data));
    return true;
}

// Функция для очистки поискового запроса
function sanitizeSearchQuery($query) {
    // Удаляем лишние пробелы и специальные символы
    $query = trim($query);
    $query = preg_replace('/[^\p{L}\p{N}\s\-\_]/u', '', $query);
    $query = preg_replace('/\s+/', ' ', $query);
    
    return $query;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Проверка лимита запросов
    if (!checkSearchRateLimit($ip)) {
        http_response_code(429);
        die("Слишком много запросов. Попробуйте позже.");
    }
    
    $query = sanitizeSearchQuery($_GET['query']);
    
    // Проверка длины запроса
    if (strlen($query) < 2) {
        echo "<p>Поисковый запрос должен содержать минимум 2 символа.</p>";
        exit;
    }
    
    if (strlen($query) > 100) {
        echo "<p>Поисковый запрос слишком длинный.</p>";
        exit;
    }
    
    try {
        // Используем подготовленные запросы для безопасности
        $stmt = $pdo->prepare("SELECT * FROM missing_pets WHERE pet_name LIKE ? OR description LIKE ? LIMIT 50");
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($results) > 0) {
            foreach ($results as $row) {
                // Экранируем вывод для предотвращения XSS
                $petName = htmlspecialchars($row['pet_name'], ENT_QUOTES, 'UTF-8');
                $petType = htmlspecialchars($row['pet_type'], ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                $contactInfo = htmlspecialchars($row['contact_info'], ENT_QUOTES, 'UTF-8');
                
                echo "<div><strong>$petName</strong> ($petType): $description<br>Контакты: $contactInfo</div>";
            }
        } else {
            echo "<p>Ничего не найдено.</p>";
        }
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        echo "<p>Произошла ошибка при поиске.</p>";
    }
    
    exit; // Завершаем выполнение после AJAX запроса
}

// Код для обычной страницы поиска
if (isset($_GET['query']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = sanitizeSearchQuery($_GET['query']);
    
    if (strlen($query) >= 2 && strlen($query) <= 100) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM missing_pets WHERE name LIKE ? OR species LIKE ? LIMIT 50");
            $searchTerm = "%$query%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $result = $stmt;
        } catch (PDOException $e) {
            error_log("Search page error: " . $e->getMessage());
            $result = null;
        }
    } else {
        $result = null;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты поиска</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <header>
        <h1>Результаты поиска</h1>
        <nav>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="add_pet.php">Добавить пропавшее животное</a></li>
                <li><a href="view_pets.php">Список пропавших и найденных животных</a></li>
                <li><a href="found_pet.php">Сообщить о найденном животном</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        if (isset($result) && $result) {
            $hasResults = false;
            while ($row = $result->fetch()) {
                $hasResults = true;
                echo "<div class='pet-card'>";
                echo "<h3>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</h3>";
                echo "<p><strong>Вид:</strong> " . htmlspecialchars($row['species'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Описание:</strong> " . htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Место пропажи:</strong> " . htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "<p><strong>Дата пропажи:</strong> " . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . "</p>";
                echo "</div>";
            }
            
            if (!$hasResults) {
                echo "<p>Ничего не найдено.</p>";
            }
        } elseif (isset($_GET['query'])) {
            if (strlen($_GET['query']) < 2) {
                echo "<p>Поисковый запрос должен содержать минимум 2 символа.</p>";
            } elseif (strlen($_GET['query']) > 100) {
                echo "<p>Поисковый запрос слишком длинный.</p>";
            } else {
                echo "<p>Произошла ошибка при поиске.</p>";
            }
        }
        ?>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <p>г. Омск, ул. Ветеринарная, д. 5</p>
                <p>📞 +7 (XXX) XXX-XX-XX</p>
                <p>✉️ vet.help@example.com</p>
                <p>🕒 Работаем: Круглосуточно</p>
            </div>
        </div>
        <p>&copy; <?php echo date('Y'); ?> Ветеринарная служба. Все права защищены.</p>
    </footer>
</body>
</html>
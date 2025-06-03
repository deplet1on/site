<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../db/bd.php';

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (!in_array($type, ['found', 'lost']) || $id <= 0) {
    echo "<p>Некорректный запрос.</p>";
    exit;
}

if ($type === 'found') {
    $stmt = $pdo->prepare("SELECT * FROM found_pets WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        echo "<p>Животное не найдено.</p>";
        exit;
    }

    $title = "Найденное животное";
    $name = $pet['name'];
    $species = $pet['species'];
    $description = $pet['description'];
    $address = $pet['approximate address'];
    if ($address == 0) {
        $address = 'отсутствует';
    } else {
        $address = $pet['approximate address'];
    }
    $date = $pet['date'];
    $photo = $pet['photo'];
    $contact = $pet['email'];
    if ($contact == 0) {
        $contact = 'отсутствует';
    } else {
        $contact = $pet['email'];
    }

} else {
    $stmt = $pdo->prepare("SELECT * FROM lost_animals WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        echo "<p>Животное не найдено.</p>";
        exit;
    }

    $title = "Пропавшее животное";
    $name = $pet['owner_name'];
    $species = $pet['animal_type'];
    $description = $pet['description'];
    $address = $pet['address'];
    if ($address == 0) {
        $address = 'отсутствует';
    } else {
        $address = $pet['address'];
    }
    $date = $pet['lost_date'];
    $photo = $pet['photo_path'];
    $contact = $pet['owner_email'];
    if ($contact == 0) {
        $contact = 'отсутствует';
    } else {
        $contact = $pet['owner_email'];
    }

}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($title); ?> — Поиск хвостиков</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="shortcut icon" href="../vkladka.png" />
    
    <!-- Дополнительные мета-теги для безопасности -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    <style>
        /* Базовая структура для фиксации футера */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
        }
        
        .page-wrapper {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }
        
        .footer {
            flex-shrink: 0;
        }
        
        .centered-section {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Адаптивные стили из index.php */
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

        /* Стили для страницы деталей */
        .pet-details {
            max-width: 700px;
            margin: 30px auto;
            padding: 25px;
            border-radius: 15px;
            background-color: rgba(0, 0, 0, 0.7);
        }
        .pet-details img {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
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
        }
    </style>
</head>
<body>

<div class="page-wrapper">
<header>
    <div class="header-container">
        <div class="logo">Поиск хвостиков</div>
        <button class="nav-toggle" aria-label="Меню">☰</button>
        <nav class="main-nav">
            <ul>
                <li><a href="../index.php">Главная</a></li>
                <li><a href="../add_pet.php">Добавить пропавшее животное</a></li>
                <li><a href="../view_pets.php">Список пропавших и найденных животных</a></li>
                <li><a href="../found_pet.php">Сообщить о найденном животном</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="centered-section">
    <main class="container">
        <div class="pet-details">
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <?php if (!empty($photo)): ?>
                <img src="../<?php echo htmlspecialchars($photo); ?>" alt="Фото животного">
            <?php else: ?>
                <img src="../default-image.jpg" alt="Фото не загружено">
            <?php endif; ?>
            <p><strong>Имя:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Вид:</strong> <?php echo htmlspecialchars($species); ?></p>
            <p><strong>Описание:</strong> <?php echo nl2br(htmlspecialchars($description)); ?></p>
            <p><strong>Адрес:</strong> <?php echo htmlspecialchars($address); ?></p>
            <p><strong>Дата:</strong> <?php echo htmlspecialchars($date); ?></p>
            <p><strong>Контакт:</strong> <a href="mailto:<?php echo htmlspecialchars($contact); ?>"><?php echo htmlspecialchars($contact); ?></a></p>
            <a href="../view_pets.php" class="btn btn-secondary mt-3">Назад</a>
        </div>
    </main>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Мобильное меню
    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }
});
</script>

</body>
</html>
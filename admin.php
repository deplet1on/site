<?php
session_start();

// Простая авторизация (в реальном проекте используйте более безопасную систему)
$admin_password = 'admin123'; // Смените пароль!

if (!isset($_SESSION['admin_logged']) && !isset($_POST['admin_password'])) {
    // Показать форму входа
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ панель - Поиск хвостиков</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3>Вход в админ панель</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Пароль:</label>
                                    <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Войти</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Проверка пароля
if (isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === $admin_password) {
        $_SESSION['admin_logged'] = true;
    } else {
        echo '<div class="alert alert-danger">Неверный пароль!</div>';
        exit;
    }
}

// Проверка авторизации
if (!isset($_SESSION['admin_logged'])) {
    header('Location: admin.php');
    exit;
}

// Подключение к БД
$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Получаем текущую секцию (по умолчанию отзывы)
$section = $_GET['section'] ?? 'reviews';
$success_message = '';

// Обработка действий для отзывов
if (isset($_POST['action']) && $section === 'reviews') {
    $review_id = (int)$_POST['review_id'];
    
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
        $stmt->execute([$review_id]);
        $success_message = "Отзыв одобрен!";
    } elseif ($_POST['action'] === 'reject') {
        $stmt = $pdo->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$review_id]);
        $success_message = "Отзыв отклонен!";
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $success_message = "Отзыв удален!";
    }
}

// Обработка действий для потерявшихся животных
if (isset($_POST['action']) && $section === 'pets') {
    $pet_id = (int)$_POST['pet_id'];
    
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE lost_animals SET status = 'approved' WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Объявление о животном одобрено!";
    } elseif ($_POST['action'] === 'reject') {
        $stmt = $pdo->prepare("UPDATE lost_animals SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Объявление о животном отклонено!";
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM lost_animals WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Объявление о животном удалено!";
    } elseif ($_POST['action'] === 'mark_found') {
        $stmt = $pdo->prepare("UPDATE lost_animals SET status = 'found' WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Животное отмечено как найденное!";
    }
}

// Получение данных в зависимости от секции
if ($section === 'reviews') {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Добавляем столбец status в запрос, если его нет
    try {
        $stmt = $pdo->query("SELECT *, COALESCE(status, 'pending') as status FROM lost_animals ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Если столбца status нет, добавляем его
        try {
            $pdo->exec("ALTER TABLE lost_animals ADD COLUMN status ENUM('pending', 'approved', 'rejected', 'found') DEFAULT 'pending'");
            $stmt = $pdo->query("SELECT * FROM lost_animals ORDER BY created_at DESC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            // Если таблица не существует или другая ошибка
            $data = [];
        }
    }
}

// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - <?= $section === 'reviews' ? 'Модерация отзывов' : 'Управление животными' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
        }
        .pet-photo {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
        }
        .coordinates {
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Боковая панель -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h4 class="mb-4">Админ панель</h4>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= $section === 'reviews' ? 'active' : '' ?>" 
                           href="?section=reviews">
                            <i class="bi bi-chat-dots"></i> Отзывы
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?= $section === 'pets' ? 'active' : '' ?>" 
                           href="?section=pets">
                            <i class="bi bi-heart"></i> Животные
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="add_pet.php" target="_blank">
                            <i class="bi bi-plus-circle"></i> Добавить животное
                        </a>
                    </li>
                </ul>
                
                <hr>
                
                <div class="mt-auto">
                    <a href="index.php" class="btn btn-secondary btn-sm mb-2 w-100">
                        <i class="bi bi-house"></i> На главную
                    </a>
                    <a href="?logout=1" class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-box-arrow-right"></i> Выйти
                    </a>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>
                        <?php if ($section === 'reviews'): ?>
                            <i class="bi bi-chat-dots"></i> Модерация отзывов
                        <?php else: ?>
                            <i class="bi bi-heart"></i> Управление животными
                        <?php endif; ?>
                    </h1>
                    <div class="badge bg-primary fs-5">
                        Всего записей: <?= count($data) ?>
                    </div>
                </div>

                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Контент для отзывов -->
                <?php if ($section === 'reviews'): ?>
                    <div class="row">
                        <?php foreach ($data as $review): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between">
                                        <h6 class="mb-0"><?= htmlspecialchars($review['name']) ?></h6>
                                        <span class="badge bg-<?= $review['status'] === 'approved' ? 'success' : ($review['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= $review['status'] === 'approved' ? 'Одобрен' : ($review['status'] === 'rejected' ? 'Отклонен' : 'На модерации') ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($review['photo']) && $review['photo'] !== 'null'): ?>
                                            <img src="uploads/<?= htmlspecialchars($review['photo']) ?>" 
                                                 class="img-thumbnail mb-3 pet-photo"
                                                 alt="Фото к отзыву">
                                        <?php endif; ?>
                                        
                                        <p class="card-text"><?= htmlspecialchars($review['review']) ?></p>
                                        <p class="mb-1"><strong>Рейтинг:</strong> 
                                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php endfor; ?>
                                            (<?= $review['rating'] ?>)
                                        </p>
                                        <p class="text-muted small">
                                            <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100" role="group">
                                            <?php if ($review['status'] !== 'approved'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm w-100" 
                                                            onclick="return confirm('Одобрить отзыв?')">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($review['status'] !== 'rejected'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-warning btn-sm w-100" 
                                                            onclick="return confirm('Отклонить отзыв?')">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display: inline;" class="flex-fill">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger btn-sm w-100" 
                                                        onclick="return confirm('Удалить отзыв навсегда?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <!-- Контент для животных -->
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($data as $pet): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bi bi-<?= strtolower($pet['animal_type']) === 'собака' ? 'dog' : (strtolower($pet['animal_type']) === 'кошка' ? 'cat' : 'heart') ?>"></i>
                                            <?= htmlspecialchars($pet['animal_type']) ?>
                                        </h6>
                                        <span class="badge bg-<?= 
                                            ($pet['status'] ?? 'pending') === 'approved' ? 'success' : 
                                            (($pet['status'] ?? 'pending') === 'rejected' ? 'danger' : 
                                            (($pet['status'] ?? 'pending') === 'found' ? 'info' : 'warning')) ?>">
                                            <?= 
                                                ($pet['status'] ?? 'pending') === 'approved' ? 'Одобрено' : 
                                                (($pet['status'] ?? 'pending') === 'rejected' ? 'Отклонено' : 
                                                (($pet['status'] ?? 'pending') === 'found' ? 'Найдено' : 'На модерации')) 
                                            ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($pet['photo_path'])): ?>
                                            <img src="<?= htmlspecialchars($pet['photo_path']) ?>" 
                                                 class="img-thumbnail mb-3 pet-photo"
                                                 alt="Фото животного">
                                        <?php endif; ?>
                                        
                                        <p class="card-text"><strong>Описание:</strong><br>
                                           <?= htmlspecialchars($pet['description']) ?></p>
                                        
                                        <p class="mb-1"><strong>Владелец:</strong> 
                                           <?= htmlspecialchars($pet['owner_name']) ?></p>
                                        
                                        <p class="mb-1"><strong>Email:</strong> 
                                           <a href="mailto:<?= htmlspecialchars($pet['owner_email']) ?>">
                                               <?= htmlspecialchars($pet['owner_email']) ?>
                                           </a></p>
                                        
                                        <p class="mb-1"><strong>Дата потери:</strong> 
                                           <?= date('d.m.Y', strtotime($pet['lost_date'])) ?></p>
                                        
                                        <p class="mb-1"><strong>Адрес:</strong> 
                                           <?= htmlspecialchars($pet['address']) ?></p>
                                        
                                        <?php if (!empty($pet['latitude']) && !empty($pet['longitude'])): ?>
                                            <p class="coordinates">
                                                <i class="bi bi-geo-alt"></i> 
                                                <?= number_format($pet['latitude'], 6) ?>, <?= number_format($pet['longitude'], 6) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <p class="text-muted small">
                                            Создано: <?= date('d.m.Y H:i', strtotime($pet['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group w-100 mb-2" role="group">
                                            <?php if (($pet['status'] ?? 'pending') !== 'approved'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm w-100" 
                                                            onclick="return confirm('Одобрить объявление?')">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (($pet['status'] ?? 'pending') !== 'found'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                    <input type="hidden" name="action" value="mark_found">
                                                    <button type="submit" class="btn btn-info btn-sm w-100" 
                                                            onclick="return confirm('Отметить как найденное?')">
                                                        <i class="bi bi-heart-fill"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="btn-group w-100" role="group">
                                            <?php if (($pet['status'] ?? 'pending') !== 'rejected'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-warning btn-sm w-100" 
                                                            onclick="return confirm('Отклонить объявление?')">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display: inline;" class="flex-fill">
                                                <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger btn-sm w-100" 
                                                        onclick="return confirm('Удалить объявление навсегда?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($data)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> 
                        <?= $section === 'reviews' ? 'Нет отзывов для модерации.' : 'Нет объявлений о животных.' ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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

// Определение текущей вкладки
$current_tab = $_GET['tab'] ?? 'reviews';

// Обработка действий для отзывов
if (isset($_POST['action']) && $_POST['type'] === 'review') {
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

// Обработка действий для найденных животных
if (isset($_POST['action']) && $_POST['type'] === 'found_pet') {
    $pet_id = (int)$_POST['pet_id'];
    
    if ($_POST['action'] === 'delete') {
        // Получаем путь к фото перед удалением
        $stmt = $pdo->prepare("SELECT photo FROM found_pets WHERE id = ?");
        $stmt->execute([$pet_id]);
        $pet_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Удаляем запись из БД
        $stmt = $pdo->prepare("DELETE FROM found_pets WHERE id = ?");
        $stmt->execute([$pet_id]);
        
        // Удаляем фото с диска, если оно есть
        if ($pet_data && !empty($pet_data['photo']) && file_exists($pet_data['photo'])) {
            unlink($pet_data['photo']);
        }
        
        $success_message = "Объявление о найденном животном удалено!";
    }
}

// Обработка действий для потерявшихся животных
if (isset($_POST['action']) && $_POST['type'] === 'lost_pet') {
    $pet_id = (int)$_POST['pet_id'];
    
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare("UPDATE lost_animals SET status = 'approved' WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Объявление о потерявшемся животном одобрено!";
    } elseif ($_POST['action'] === 'reject') {
        $stmt = $pdo->prepare("UPDATE lost_animals SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Объявление о потерявшемся животном отклонено!";
    } elseif ($_POST['action'] === 'delete') {
        // Получаем путь к фото перед удалением
        $stmt = $pdo->prepare("SELECT photo_path FROM lost_animals WHERE id = ?");
        $stmt->execute([$pet_id]);
        $pet_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Удаляем запись из БД
        $stmt = $pdo->prepare("DELETE FROM lost_animals WHERE id = ?");
        $stmt->execute([$pet_id]);
        
        // Удаляем фото с диска, если оно есть
        if ($pet_data && !empty($pet_data['photo_path']) && file_exists($pet_data['photo_path'])) {
            unlink($pet_data['photo_path']);
        }
        
        $success_message = "Объявление о потерявшемся животном удалено!";
    } elseif ($_POST['action'] === 'mark_found') {
        $stmt = $pdo->prepare("UPDATE lost_animals SET status = 'found' WHERE id = ?");
        $stmt->execute([$pet_id]);
        $success_message = "Животное отмечено как найденное!";
    }
}

// Обработка редактирования найденного животного
if (isset($_POST['action']) && $_POST['action'] === 'edit_found_pet') {
    $pet_id = (int)$_POST['pet_id'];
    $species = htmlspecialchars(trim($_POST['species']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $email = trim($_POST['email']);
    $approximate_address = htmlspecialchars(trim($_POST['approximate_address']), ENT_QUOTES, 'UTF-8');
    $date = $_POST['date'];
    
    // Валидация email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Некорректный email адрес";
    } else {
        $stmt = $pdo->prepare("UPDATE found_pets SET species = ?, description = ?, email = ?, `approximate address` = ?, date = ? WHERE id = ?");
        $stmt->execute([$species, $description, $email, $approximate_address, $date, $pet_id]);
        $success_message = "Данные о найденном животном обновлены!";
    }
}

// Обработка редактирования потерявшегося животного
if (isset($_POST['action']) && $_POST['action'] === 'edit_lost_pet') {
    $pet_id = (int)$_POST['pet_id'];
    $animal_type = htmlspecialchars(trim($_POST['animal_type']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $owner_name = htmlspecialchars(trim($_POST['owner_name']), ENT_QUOTES, 'UTF-8');
    $owner_email = trim($_POST['owner_email']);
    $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');
    $lost_date = $_POST['lost_date'];
    
    // Валидация email
    if (!filter_var($owner_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Некорректный email адрес";
    } else {
        $stmt = $pdo->prepare("UPDATE lost_animals SET animal_type = ?, description = ?, owner_name = ?, owner_email = ?, address = ?, lost_date = ? WHERE id = ?");
        $stmt->execute([$animal_type, $description, $owner_name, $owner_email, $address, $lost_date, $pet_id]);
        $success_message = "Данные о потерявшемся животном обновлены!";
    }
}

// Получение отзывов для модерации
$stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение найденных животных
$stmt = $pdo->query("SELECT * FROM found_pets ORDER BY date DESC");
$found_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение потерявшихся животных
try {
    // Проверяем, есть ли столбец status, если нет - добавляем его
    $stmt = $pdo->query("SHOW COLUMNS FROM lost_animals LIKE 'status'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE lost_animals ADD COLUMN status ENUM('pending', 'approved', 'rejected', 'found') DEFAULT 'pending'");
    }
    
    $stmt = $pdo->query("SELECT * FROM lost_animals ORDER BY created_at DESC");
    $lost_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lost_pets = [];
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
    <title>Админ панель - Поиск хвостиков</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pet-photo {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }
        .coordinates {
            font-size: 0.9em;
            color: #6c757d;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Заголовок админ панели -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="bi bi-gear-fill"></i> Админ панель</h1>
                    <p class="mb-0">Управление контентом сайта "Поиск хвостиков"</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light me-2">
                        <i class="bi bi-house"></i> На главную
                    </a>
                    <a href="?logout=1" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Выйти
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-chat-square-text text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <div>
                            <div class="stats-number"><?= count($reviews) ?></div>
                            <div class="text-muted">Всего отзывов</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-heart text-success" style="font-size: 3rem;"></i>
                        </div>
                        <div>
                            <div class="stats-number"><?= count($found_pets) ?></div>
                            <div class="text-muted">Найденных животных</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-search-heart text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <div>
                            <div class="stats-number"><?= count($lost_pets) ?></div>
                            <div class="text-muted">Потерявшихся животных</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Вкладки навигации -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $current_tab === 'reviews' ? 'active' : '' ?>" 
                        onclick="location.href='?tab=reviews'">
                    <i class="bi bi-chat-square-text"></i> Модерация отзывов
                    <?php 
                    $pending_reviews = array_filter($reviews, function($r) { return $r['status'] === 'pending'; });
                    if (count($pending_reviews) > 0): 
                    ?>
                        <span class="badge bg-warning ms-1"><?= count($pending_reviews) ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $current_tab === 'found_pets' ? 'active' : '' ?>" 
                        onclick="location.href='?tab=found_pets'">
                    <i class="bi bi-heart"></i> Найденные животные
                    <span class="badge bg-info ms-1"><?= count($found_pets) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $current_tab === 'lost_pets' ? 'active' : '' ?>" 
                        onclick="location.href='?tab=lost_pets'">
                    <i class="bi bi-search-heart"></i> Потерявшиеся животные
                    <?php 
                    $pending_lost = array_filter($lost_pets, function($p) { return ($p['status'] ?? 'pending') === 'pending'; });
                    if (count($pending_lost) > 0): 
                    ?>
                        <span class="badge bg-warning ms-1"><?= count($pending_lost) ?></span>
                    <?php endif; ?>
                    <span class="badge bg-secondary ms-1"><?= count($lost_pets) ?></span>
                </button>
            </li>
        </ul>

        <!-- Содержимое вкладок -->
        <div class="tab-content" id="adminTabsContent">
            
            <!-- Вкладка модерации отзывов -->
            <?php if ($current_tab === 'reviews'): ?>
                <div class="tab-pane fade show active" id="reviews-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><i class="bi bi-chat-square-text"></i> Модерация отзывов</h3>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="btnradio1">Все</label>
                            <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off">
                            <label class="btn btn-outline-warning" for="btnradio2">На модерации</label>
                            <input type="radio" class="btn-check" name="btnradio" id="btnradio3" autocomplete="off">
                            <label class="btn btn-outline-success" for="btnradio3">Одобрены</label>
                        </div>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($reviews as $review): ?>
                            <div class="col-md-6 mb-4 review-card" data-status="<?= $review['status'] ?>">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between">
                                        <h5 class="mb-0"><?= htmlspecialchars($review['name']) ?></h5>
                                        <span class="badge bg-<?= $review['status'] === 'approved' ? 'success' : ($review['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                            <?= $review['status'] === 'approved' ? 'Одобрен' : ($review['status'] === 'rejected' ? 'Отклонен' : 'На модерации') ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($review['photo']) && $review['photo'] !== 'null'): ?>
                                            <img src="uploads/<?= htmlspecialchars($review['photo']) ?>" 
                                                 class="img-thumbnail mb-3" 
                                                 style="max-width: 150px;"
                                                 alt="Фото к отзыву">
                                        <?php endif; ?>
                                        
                                        <p><strong>Отзыв:</strong> <?= htmlspecialchars($review['review']) ?></p>
                                        <p><strong>Рейтинг:</strong> 
                                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                                <i class="bi bi-star-fill text-warning"></i>
                                            <?php endfor; ?>
                                            (<?= $review['rating'] ?>)
                                        </p>
                                        <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></p>
                                        
                                        <div class="btn-group w-100" role="group">
                                            <?php if ($review['status'] !== 'approved'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="type" value="review">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Одобрить отзыв?')">
                                                        <i class="bi bi-check"></i> Одобрить
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($review['status'] !== 'rejected'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="type" value="review">
                                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-warning btn-sm w-100" onclick="return confirm('Отклонить отзыв?')">
                                                        <i class="bi bi-x"></i> Отклонить
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display: inline;" class="flex-fill">
                                                <input type="hidden" name="type" value="review">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Удалить отзыв навсегда?')">
                                                    <i class="bi bi-trash"></i> Удалить
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Нет отзывов для модерации.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Вкладка найденных животных -->
            <?php if ($current_tab === 'found_pets'): ?>
                <div class="tab-pane fade show active" id="found-pets-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><i class="bi bi-heart"></i> Управление найденными животными</h3>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Обновить
                            </button>
                        </div>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($found_pets as $pet): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-heart text-success"></i>
                                            <?= htmlspecialchars($pet['species']) ?>
                                        </h5>
                                        <small class="text-muted">ID: <?= $pet['id'] ?></small>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($pet['photo']) && file_exists($pet['photo'])): ?>
                                            <img src="<?= htmlspecialchars($pet['photo']) ?>" 
                                                 class="pet-photo mb-3" 
                                                 alt="Фото найденного животного">
                                        <?php else: ?>
                                            <div class="alert alert-light text-center mb-3">
                                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                                <p class="mb-0 text-muted">Фото не загружено</p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <p><strong>Описание:</strong> 
                                            <?= !empty($pet['description']) ? htmlspecialchars($pet['description']) : 'Описание не указано' ?>
                                        </p>
                                        <p><strong>Email:</strong> 
                                            <a href="mailto:<?= htmlspecialchars($pet['email']) ?>">
                                                <?= htmlspecialchars($pet['email']) ?>
                                            </a>
                                        </p>
                                        <p><strong>Адрес:</strong> <?= htmlspecialchars($pet['approximate address']) ?></p>
                                        <p><strong>Дата находки:</strong> <?= date('d.m.Y', strtotime($pet['date'])) ?></p>
                                        
                                        <?php if (!empty($pet['latitude']) && !empty($pet['longitude'])): ?>
                                            <p class="coordinates">
                                                <strong>Координаты:</strong> 
                                                <?= htmlspecialchars($pet['latitude']) ?>, <?= htmlspecialchars($pet['longitude']) ?>
                                                <a href="https://yandex.ru/maps/?ll=<?= htmlspecialchars($pet['longitude']) ?>,<?= htmlspecialchars($pet['latitude']) ?>&z=16&pt=<?= htmlspecialchars($pet['longitude']) ?>,<?= htmlspecialchars($pet['latitude']) ?>,pm2rdm" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                                    <i class="bi bi-geo-alt"></i> Карта
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editFoundModal<?= $pet['id'] ?>">
                                                <i class="bi bi-pencil"></i> Редактировать
                                            </button>
                                            
                                            <form method="POST" style="display: inline;" class="flex-fill">
                                                <input type="hidden" name="type" value="found_pet">
                                                <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger btn-sm w-100" 
                                                        onclick="return confirm('Удалить объявление о найденном животном?')">
                                                    <i class="bi bi-trash"></i> Удалить
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Модальное окно редактирования найденного животного -->
                            <div class="modal fade" id="editFoundModal<?= $pet['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактировать найденное животное</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit_found_pet">
                                                <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label for="species<?= $pet['id'] ?>" class="form-label">Вид животного:</label>
                                                    <input type="text" class="form-control" id="species<?= $pet['id'] ?>" 
                                                           name="species" value="<?= htmlspecialchars($pet['species']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="description<?= $pet['id'] ?>" class="form-label">Описание:</label>
                                                    <textarea class="form-control" id="description<?= $pet['id'] ?>" 
                                                              name="description" rows="3"><?= htmlspecialchars($pet['description']) ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="email<?= $pet['id'] ?>" class="form-label">Email:</label>
                                                    <input type="email" class="form-control" id="email<?= $pet['id'] ?>" 
                                                           name="email" value="<?= htmlspecialchars($pet['email']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="address<?= $pet['id'] ?>" class="form-label">Адрес:</label>
                                                    <input type="text" class="form-control" id="address<?= $pet['id'] ?>" 
                                                           name="approximate_address" value="<?= htmlspecialchars($pet['approximate address']) ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="date<?= $pet['id'] ?>" class="form-label">Дата находки:</label>
                                                    <input type="date" class="form-control" id="date<?= $pet['id'] ?>" 
                                                           name="date" value="<?= $pet['date'] ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($found_pets)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Нет объявлений о найденных животных.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Вкладка потерявшихся животных -->
            <?php if ($current_tab === 'lost_pets'): ?>
                <div class="tab-pane fade show active" id="lost-pets-tab">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3><i class="bi bi-search-heart"></i> Управление потерявшимися животными</h3>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="lostradio" id="lostradio1" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="lostradio1">Все</label>
                            <input type="radio" class="btn-check" name="lostradio" id="lostradio2" autocomplete="off">
                            <label class="btn btn-outline-warning" for="lostradio2">На модерации</label>
                            <input type="radio" class="btn-check" name="lostradio" id="lostradio3" autocomplete="off">
                            <label class="btn btn-outline-success" for="lostradio3">Одобрены</label>
                            <input type="radio" class="btn-check" name="lostradio" id="lostradio4" autocomplete="off">
                            <label class="btn btn-outline-info" for="lostradio4">Найдены</label>
                        </div>
                    </div>
                    
                    <div class="row">
                        <?php foreach ($lost_pets as $pet): ?>
                            <div class="col-lg-6 mb-4 lost-pet-card" data-status="<?= $pet['status'] ?? 'pending' ?>">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="bi bi-search-heart text-warning"></i>
                                            <?= htmlspecialchars($pet['animal_type']) ?>
                                        </h5>
                                        <div>
                                            <span class="badge bg-<?= 
                                                ($pet['status'] ?? 'pending') === 'approved' ? 'success' : 
                                                (($pet['status'] ?? 'pending') === 'rejected' ? 'danger' : 
                                                (($pet['status'] ?? 'pending') === 'found' ? 'info' : 'warning')) ?>">
                                                <?= 
                                                    ($pet['status'] ?? 'pending') === 'approved' ? 'Одобрено' : 
                                                    (($pet['status'] ?? 'pending') === 'rejected' ? 'Отклонено' : 
                                                    (($pet['status'] ?? 'pending') === 'found' ? 'Найдено' : 'На модерации')) ?>
                                            </span>
                                            <small class="text-muted ms-2">ID: <?= $pet['id'] ?></small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($pet['photo_path']) && file_exists($pet['photo_path'])): ?>
                                            <img src="<?= htmlspecialchars($pet['photo_path']) ?>" 
                                                 class="pet-photo mb-3" 
                                                 alt="Фото потерявшегося животного">
                                        <?php else: ?>
                                            <div class="alert alert-light text-center mb-3">
                                                <i class="bi bi-image text-muted" style="font-size: 2rem;"></i>
                                                <p class="mb-0 text-muted">Фото не загружено</p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <p><strong>Описание:</strong> <?= htmlspecialchars($pet['description']) ?></p>
                                        <p><strong>Владелец:</strong> <?= htmlspecialchars($pet['owner_name']) ?></p>
                                        <p><strong>Email:</strong> 
                                            <a href="mailto:<?= htmlspecialchars($pet['owner_email']) ?>">
                                                <?= htmlspecialchars($pet['owner_email']) ?>
                                            </a>
                                        </p>
                                        <p><strong>Адрес потери:</strong> <?= htmlspecialchars($pet['address']) ?></p>
                                        <p><strong>Дата потери:</strong> <?= date('d.m.Y', strtotime($pet['lost_date'])) ?></p>
                                        <p><strong>Дата подачи:</strong> <?= date('d.m.Y H:i', strtotime($pet['created_at'])) ?></p>
                                        
                                        <div class="btn-group w-100 mb-2" role="group">
                                            <?php if (($pet['status'] ?? 'pending') !== 'approved'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="type" value="lost_pet">
                                                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm w-100" 
                                                            onclick="return confirm('Одобрить объявление?')">
                                                        <i class="bi bi-check"></i> Одобрить
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (($pet['status'] ?? 'pending') !== 'rejected'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="type" value="lost_pet">
                                                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-warning btn-sm w-100" 
                                                            onclick="return confirm('Отклонить объявление?')">
                                                        <i class="bi bi-x"></i> Отклонить
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="btn-group w-100" role="group">
                                            <?php if (($pet['status'] ?? 'pending') !== 'found'): ?>
                                                <form method="POST" style="display: inline;" class="flex-fill">
                                                    <input type="hidden" name="type" value="lost_pet">
                                                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                    <input type="hidden" name="action" value="mark_found">
                                                    <button type="submit" class="btn btn-info btn-sm w-100" 
                                                            onclick="return confirm('Отметить как найденное?')">
                                                        <i class="bi bi-heart-fill"></i> Найдено
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editLostModal<?= $pet['id'] ?>">
                                                <i class="bi bi-pencil"></i> Редактировать
                                            </button>
                                            
                                            <form method="POST" style="display: inline;" class="flex-fill">
                                                <input type="hidden" name="type" value="lost_pet">
                                                <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger btn-sm w-100" 
                                                        onclick="return confirm('Удалить объявление?')">
                                                    <i class="bi bi-trash"></i> Удалить
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Модальное окно редактирования потерявшегося животного -->
                            <div class="modal fade" id="editLostModal<?= $pet['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Редактировать потерявшееся животное</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="edit_lost_pet">
                                                <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label for="animal_type<?= $pet['id'] ?>" class="form-label">Тип животного:</label>
                                                    <input type="text" class="form-control" id="animal_type<?= $pet['id'] ?>" 
                                                           name="animal_type" value="<?= htmlspecialchars($pet['animal_type']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="lost_description<?= $pet['id'] ?>" class="form-label">Описание:</label>
                                                    <textarea class="form-control" id="lost_description<?= $pet['id'] ?>" 
                                                              name="description" rows="3"><?= htmlspecialchars($pet['description']) ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="owner_name<?= $pet['id'] ?>" class="form-label">Имя владельца:</label>
                                                    <input type="text" class="form-control" id="owner_name<?= $pet['id'] ?>" 
                                                           name="owner_name" value="<?= htmlspecialchars($pet['owner_name']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="owner_email<?= $pet['id'] ?>" class="form-label">Email владельца:</label>
                                                    <input type="email" class="form-control" id="owner_email<?= $pet['id'] ?>" 
                                                           name="owner_email" value="<?= htmlspecialchars($pet['owner_email']) ?>" required>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="lost_address<?= $pet['id'] ?>" class="form-label">Адрес потери:</label>
                                                    <input type="text" class="form-control" id="lost_address<?= $pet['id'] ?>" 
                                                           name="address" value="<?= htmlspecialchars($pet['address']) ?>">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="lost_date<?= $pet['id'] ?>" class="form-label">Дата потери:</label>
                                                    <input type="date" class="form-control" id="lost_date<?= $pet['id'] ?>" 
                                                           name="lost_date" value="<?= $pet['lost_date'] ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (empty($lost_pets)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Нет объявлений о потерявшихся животных.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Подключение Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript для фильтрации -->
    <script>
        // Фильтрация отзывов
        document.querySelectorAll('input[name="btnradio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const status = this.id === 'btnradio2' ? 'pending' : 
                              (this.id === 'btnradio3' ? 'approved' : 'all');
                
                document.querySelectorAll('.review-card').forEach(card => {
                    if (status === 'all' || card.dataset.status === status) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Фильтрация потерявшихся животных
        document.querySelectorAll('input[name="lostradio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const status = this.id === 'lostradio2' ? 'pending' : 
                              (this.id === 'lostradio3' ? 'approved' : 
                              (this.id === 'lostradio4' ? 'found' : 'all'));
                
                document.querySelectorAll('.lost-pet-card').forEach(card => {
                    if (status === 'all' || card.dataset.status === status) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Автоматическое скрытие уведомлений
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
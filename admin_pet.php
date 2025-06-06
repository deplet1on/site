<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Простая проверка авторизации
$admin_password = 'your_secure_admin_password_here'; // Замените на свой пароль

if (!isset($_SESSION['admin_logged_in'])) {
    if (isset($_POST['admin_password'])) {
        if ($_POST['admin_password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
        } else {
            $error = 'Неверный пароль';
        }
    }
    
    if (!isset($_SESSION['admin_logged_in'])) {
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Админ-панель</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
            <style>
                body { background-color: #f8f9fa; }
                .login-container { 
                    max-width: 400px; 
                    margin: 100px auto; 
                    background: white; 
                    padding: 30px; 
                    border-radius: 10px; 
                    box-shadow: 0 0 20px rgba(0,0,0,0.1); 
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h3 class="text-center mb-4">Вход в админ-панель</h3>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="admin_password" class="form-label">Пароль администратора:</label>
                        <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Обработка действий
if (isset($_POST['action'])) {
    $host = 'localhost';
    $dbname = 'a1120713_11';
    $username = 'a1120713_11';
    $password = 'tumupiguzi';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        switch ($_POST['action']) {
            case 'delete':
                $id = (int)$_POST['id'];
                // Удаляем фото, если оно есть
                $stmt = $pdo->prepare("SELECT photo_path FROM lost_animals WHERE id = ?");
                $stmt->execute([$id]);
                $pet = $stmt->fetch();
                
                if ($pet && $pet['photo_path'] && file_exists($pet['photo_path'])) {
                    unlink($pet['photo_path']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM lost_animals WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Объявление удалено";
                break;

            case 'toggle_status':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE lost_animals SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Статус изменен";
                break;

            case 'update':
                $id = (int)$_POST['id'];
                $animal_type = htmlspecialchars(trim($_POST['animal_type']), ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
                $owner_name = htmlspecialchars(trim($_POST['owner_name']), ENT_QUOTES, 'UTF-8');
                $owner_email = trim($_POST['owner_email']);
                $address = htmlspecialchars(trim($_POST['address']), ENT_QUOTES, 'UTF-8');
                $lost_date = $_POST['lost_date'];
                
                $stmt = $pdo->prepare("UPDATE lost_animals SET animal_type = ?, description = ?, owner_name = ?, owner_email = ?, address = ?, lost_date = ? WHERE id = ?");
                $stmt->execute([$animal_type, $description, $owner_name, $owner_email, $address, $lost_date, $id]);
                $success = "Объявление обновлено";
                break;
        }
    } catch (PDOException $e) {
        $error = "Ошибка базы данных: " . $e->getMessage();
    }
}

// Получение списка объявлений
$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем статистику
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_count,
        SUM(CASE WHEN DATE(created_at) = CURRENT_DATE THEN 1 ELSE 0 END) as today_count
        FROM lost_animals");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Получаем все объявления
    $stmt = $pdo->query("SELECT * FROM lost_animals ORDER BY created_at DESC");
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Ошибка подключения к базе данных: " . $e->getMessage();
    $pets = [];
    $stats = ['total' => 0, 'active_count' => 0, 'inactive_count' => 0, 'today_count' => 0];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Управление объявлениями</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .pet-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .pet-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-active { background-color: #d4edda; color: #155724; }
        .status-inactive { background-color: #f8d7da; color: #721c24; }
        .btn-sm { margin: 2px; }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .edit-form {
            display: none;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .pet-details {
            font-size: 0.9rem;
        }
        .pet-details strong {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h1><i class="fas fa-paw"></i> Админ-панель - Управление объявлениями</h1>
                    <p class="mb-0">Управление объявлениями о пропавших животных</p>
                </div>
                <div class="col-auto">
                    <a href="?logout=1" class="btn btn-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Выход
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Статистика -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="text-muted">Всего объявлений</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-success"><?= $stats['active_count'] ?></div>
                    <div class="text-muted">Активных</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-warning"><?= $stats['inactive_count'] ?></div>
                    <div class="text-muted">Неактивных</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-info"><?= $stats['today_count'] ?></div>
                    <div class="text-muted">Сегодня</div>
                </div>
            </div>
        </div>

        <!-- Список объявлений -->
        <div class="row">
            <div class="col-12">
                <h3><i class="fas fa-list"></i> Все объявления (<?= count($pets) ?>)</h3>
                
                <?php if (empty($pets)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Объявлений пока нет
                    </div>
                <?php else: ?>
                    <?php foreach ($pets as $pet): ?>
                        <div class="pet-card">
                            <div class="row">
                                <div class="col-md-2">
                                    <?php if ($pet['photo_path'] && file_exists($pet['photo_path'])): ?>
                                        <img src="<?= htmlspecialchars($pet['photo_path']) ?>" 
                                             alt="Фото животного" class="pet-photo">
                                    <?php else: ?>
                                        <div class="pet-photo bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-7">
                                    <div class="pet-details">
                                        <h5 class="mb-2">
                                            <?= htmlspecialchars($pet['animal_type']) ?>
                                            <span class="status-badge <?= $pet['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                                <?= $pet['is_active'] ? 'Активно' : 'Неактивно' ?>
                                            </span>
                                        </h5>
                                        
                                        <p class="mb-1"><strong>Описание:</strong> 
                                           <?= htmlspecialchars(mb_substr($pet['description'], 0, 100)) ?><?= mb_strlen($pet['description']) > 100 ? '...' : '' ?>
                                        </p>
                                        
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong>Владелец:</strong> <?= htmlspecialchars($pet['owner_name']) ?></p>
                                                <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($pet['owner_email']) ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <p class="mb-1"><strong>Дата потери:</strong> <?= date('d.m.Y', strtotime($pet['lost_date'])) ?></p>
                                                <p class="mb-1"><strong>Добавлено:</strong> <?= date('d.m.Y H:i', strtotime($pet['created_at'])) ?></p>
                                            </div>
                                        </div>
                                        
                                        <p class="mb-1"><strong>Адрес:</strong> <?= htmlspecialchars($pet['address']) ?></p>
                                        
                                        <?php if ($pet['latitude'] && $pet['longitude']): ?>
                                            <p class="mb-1"><strong>Координаты:</strong> 
                                               <?= round($pet['latitude'], 6) ?>, <?= round($pet['longitude'], 6) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?= $pet['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $pet['is_active'] ? 'btn-warning' : 'btn-success' ?> mb-2">
                                                <i class="fas <?= $pet['is_active'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                                <?= $pet['is_active'] ? 'Скрыть' : 'Показать' ?>
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-sm btn-info mb-2" 
                                                onclick="toggleEdit(<?= $pet['id'] ?>)">
                                            <i class="fas fa-edit"></i> Редактировать
                                        </button>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Вы уверены, что хотите удалить это объявление?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $pet['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Удалить
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Форма редактирования -->
                            <div id="edit-form-<?= $pet['id'] ?>" class="edit-form">
                                <h6><i class="fas fa-edit"></i> Редактирование объявления</h6>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $pet['id'] ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Тип животного:</label>
                                                <select name="animal_type" class="form-control form-control-sm" required>
                                                    <option value="Собака" <?= $pet['animal_type'] == 'Собака' ? 'selected' : '' ?>>Собака</option>
                                                    <option value="Кошка" <?= $pet['animal_type'] == 'Кошка' ? 'selected' : '' ?>>Кошка</option>
                                                    <option value="Попугай" <?= $pet['animal_type'] == 'Попугай' ? 'selected' : '' ?>>Попугай</option>
                                                    <option value="Хомяк" <?= $pet['animal_type'] == 'Хомяк' ? 'selected' : '' ?>>Хомяк</option>
                                                    <option value="Кролик" <?= $pet['animal_type'] == 'Кролик' ? 'selected' : '' ?>>Кролик</option>
                                                    <option value="Черепаха" <?= $pet['animal_type'] == 'Черепаха' ? 'selected' : '' ?>>Черепаха</option>
                                                    <option value="Другое" <?= $pet['animal_type'] == 'Другое' ? 'selected' : '' ?>>Другое</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Имя владельца:</label>
                                                <input type="text" name="owner_name" class="form-control form-control-sm" 
                                                       value="<?= htmlspecialchars($pet['owner_name']) ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Email:</label>
                                                <input type="email" name="owner_email" class="form-control form-control-sm" 
                                                       value="<?= htmlspecialchars($pet['owner_email']) ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Дата потери:</label>
                                                <input type="date" name="lost_date" class="form-control form-control-sm" 
                                                       value="<?= $pet['lost_date'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Адрес:</label>
                                                <input type="text" name="address" class="form-control form-control-sm" 
                                                       value="<?= htmlspecialchars($pet['address']) ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Описание:</label>
                                        <textarea name="description" class="form-control form-control-sm" rows="3" 
                                                  maxlength="1000"><?= htmlspecialchars($pet['description']) ?></textarea>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-save"></i> Сохранить
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                onclick="toggleEdit(<?= $pet['id'] ?>)">
                                            <i class="fas fa-times"></i> Отмена
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleEdit(id) {
            const form = document.getElementById('edit-form-' + id);
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }

        // Автоматическое скрытие алертов через 5 секунд
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                }
            });
        }, 5000);
    </script>
</body>
</html>

<?php
// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
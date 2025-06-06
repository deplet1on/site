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

// Обработка действий
if (isset($_POST['action'])) {
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

// Получение отзывов для модерации
$stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ панель - Модерация отзывов</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Модерация отзывов</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">На главную</a>
                <a href="?logout=1" class="btn btn-danger">Выйти</a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($reviews as $review): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5><?= htmlspecialchars($review['name']) ?></h5>
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
                            
                            <div class="btn-group" role="group">
                                <?php if ($review['status'] !== 'approved'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Одобрить отзыв?')">
                                            <i class="bi bi-check"></i> Одобрить
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($review['status'] !== 'rejected'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Отклонить отзыв?')">
                                            <i class="bi bi-x"></i> Отклонить
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Удалить отзыв навсегда?')">
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
            <div class="alert alert-info">Нет отзывов для модерации.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
?>
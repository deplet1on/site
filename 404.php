<?php
// В PHP-коде отдаём правильный статус 404, чтобы поисковик понял, что это страница ошибки
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Страница не найдена</title>
  <link rel="stylesheet" href="styles.css"> <!-- если требуется тот же CSS -->
</head>
<body>
  <header>
    <h1>Ошибка 404</h1>
  </header>
  <main>
    <p>Извините, запрошенная страница не найдена.</p>
    <p>Вернуться на <a href="/">главную страницу</a>.</p>
    <!-- Можно добавить поиск по сайту или карту сайта -->
  </main>
  <footer>
    <p>&copy; 2025 Ваш Сайт</p>
  </footer>
</body>
</html>

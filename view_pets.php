<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'db/bd.php';

$found_stmt = $pdo->query("SELECT * FROM found_pets");
$found_pets = $found_stmt->fetchAll(PDO::FETCH_ASSOC);

$lost_stmt = $pdo->query("SELECT * FROM lost_animals");
$lost_animals = $lost_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список животных — Поиск хвостиков</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        
        .flex-grow-1 {
            flex: 1 0 auto;
        }
        
        .footer {
            flex-shrink: 0;
            width: 100%;
        }

        .card {
            background-color: #fff;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 0px;
            max-width: 300px;
            margin: 0px auto;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-img-top {
            object-fit: cover;
            height: 200px;
            border-radius: 8px 8px 0 0;
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.2rem;
            color: #2c3e50;
        }

        .card-text {
            color: #555;
            flex-grow: 1;
        }

        .section-bg {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 30px;
        }
        
        /* Выравнивание столбцов по высоте */
        .columns-container {
            display: flex;
            flex-wrap: wrap;
        }
        
        .pet-column {
            flex: 1;
            min-width: 300px;
            padding: 0 15px;
        }
        
        /* Адаптивные исправления */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            padding: 10px 15px;
        }
        
        .logo {
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            font-weight: bold;
            color: white;
        }
        
        .nav-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
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
            white-space: nowrap;
        }
        
        .main-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        @media (max-width: 992px) {
            .header-container {
                flex-direction: column;
                align-items: stretch;
            }
            
            .nav-toggle {
                display: block;
                position: absolute;
                top: 15px;
                right: 15px;
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
            
            .card {
                max-width: 100%;
            }
            
            .pet-column {
                min-width: 100%;
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
<body style="display: flex; flex-direction: column; min-height: 100vh;">
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
<div class="flex-grow-1">
    <div class="centered-section section-bg">
        <div class="container">
            <div class="columns-container">
                <!-- Колонка найденных животных -->
                <div class="pet-column">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h2 class="text-center">Найденные животные</h2>
                        </div>
                        <?php foreach ($found_pets as $pet): ?>
                            <div class="col-md-12 mb-4">
                                <div class="card">
                                    <?php if ($pet['photo']): ?>
                                        <img src="<?= htmlspecialchars($pet['photo']) ?>" class="card-img-top" alt="Фото животного">
                                    <?php else: ?>
                                        <img src="default-image.jpg" class="card-img-top" alt="Фото не загружено">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($pet['name'] ?? '') ?> (<?= htmlspecialchars($pet['species']) ?>)</h5>
                                        <p class="card-text"><?= htmlspecialchars($pet['description']) ?></p>
                                        <a href="db/pet_details.php?type=found&id=<?= htmlspecialchars($pet['id']) ?>" class="btn btn-success mt-auto">Подробнее</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Колонка пропавших животных -->
                <div class="pet-column">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <h2 class="text-center">Пропавшие животные</h2>
                        </div>
                        <?php foreach ($lost_animals as $pet): ?>
                            <div class="col-md-12 mb-4">
                                <div class="card">
                                    <?php if ($pet['photo_path']): ?>
                                        <img src="<?= htmlspecialchars($pet['photo_path']) ?>" class="card-img-top" alt="Фото животного">
                                    <?php else: ?>
                                        <img src="default-image.jpg" class="card-img-top" alt="Фото не загружено">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($pet['animal_type']) ?> — Потерялся</h5>
                                        <p class="card-text"><?= htmlspecialchars($pet['description']) ?></p>
                                        <a href="db/pet_details.php?type=lost&id=<?= htmlspecialchars($pet['id']) ?>" class="btn btn-danger mt-auto">Подробнее</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
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

<script>
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
</body>
</html>
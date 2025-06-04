<?php
// shelters.php
$page_title = "Приюты — Поиск хвостиков";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $page_title ?></title>
    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Подключение Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Подключение собственного CSS -->
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="vkladka.png" />
    
    <!-- Дополнительные мета-теги для безопасности -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    <!-- Подключаем API Яндекс.Карт -->
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=a97f57d3-dc31-4396-ac2d-78d71b4aced3" type="text/javascript"></script>
    
    <style>
        /* Адаптивные исправления из index.php */
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
        
        .main-nav a:hover, .main-nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        /* Стили для блоков приютов */
        .shelter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .shelter-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            border-radius: 15px;
            padding: 25px;
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .shelter-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        .shelter-card h3 {
            color: #ffffff;
            margin-bottom: 15px;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .shelter-info {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .shelter-info i {
            color: #ffa500;
            font-size: 1.1rem;
            width: 20px;
        }
        
        .shelter-info strong {
            color: #ffffff;
        }
        
        .map-section {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            color: white;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .map-section h2 {
            color: #ffffff;
            margin-bottom: 20px;
            text-align: center;
        }
        
        #map {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .page-title {
            text-align: center;
            color: #ffffff;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
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
            
            .page-title {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .shelter-grid {
                grid-template-columns: 1fr;
            }
            
            .shelter-card {
                padding: 20px;
            }
            
            .map-section {
                padding: 20px;
            }
            
            #map {
                height: 300px;
            }
        }
    </style>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
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

    <script async src="https://www.googletagmanager.com/gtag/js?id=G-SFL7Y734H5"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){ dataLayer.push(arguments); }
      gtag('js', new Date());
      gtag('config', 'G-SFL7Y734H5');
    </script>
</head>
<body>
    <!-- Шапка как в index.php -->
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
                    <li><a href="shelter.php" class="active">Приюты</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="centered-section">
        <h1 class="page-title">Приюты животных в г. Омск</h1>

        <!-- Блок с картой -->
        <section class="map-section">
            <h2><i class="bi bi-geo-alt"></i> Карта приютов</h2>
            <div id="map"></div>
        </section>

        <!-- Список приютов в виде карточек -->
        <section class="shelter-section">
            <div class="shelter-grid">
                <div class="shelter-card">
                    <h3><i class="bi bi-house-heart"></i> Муниципальный приют для животных «Друг»</h3>
                    <div class="shelter-info">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><strong>Адрес:</strong> 2-я Учхозная улица, 2А</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-telephone-fill"></i>
                        <span><strong>Телефон:</strong> не указан</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-pin-map-fill"></i>
                        <span><strong>Координаты:</strong> 55.005847, 73.463146</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-clock-fill"></i>
                        <span><strong>Режим работы:</strong> Ежедневно с 9:00 до 18:00</span>
                    </div>
                </div>

                <div class="shelter-card">
                    <h3><i class="bi bi-heart-fill"></i> Приют «Омские хвостики»</h3>
                    <div class="shelter-info">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><strong>Адрес:</strong> 1-я Казахстанская улица, 3, стр. 2</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-telephone-fill"></i>
                        <span><strong>Телефон:</strong> +7 (3812) 49-87-66</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-pin-map-fill"></i>
                        <span><strong>Координаты:</strong> 54.909580, 73.306381</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-clock-fill"></i>
                        <span><strong>Режим работы:</strong> Пн-Пт: 10:00-17:00, Сб-Вс: 11:00-16:00</span>
                    </div>
                </div>

                <div class="shelter-card">
                    <h3><i class="bi bi-award-fill"></i> Приют «Джульбарс и его друзья»</h3>
                    <div class="shelter-info">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><strong>Адрес:</strong> улица Масленникова, 80</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-telephone-fill"></i>
                        <span><strong>Телефон:</strong> не указан</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-pin-map-fill"></i>
                        <span><strong>Координаты:</strong> 54.974013, 73.409499</span>
                    </div>
                    <div class="shelter-info">
                        <i class="bi bi-clock-fill"></i>
                        <span><strong>Режим работы:</strong> Ежедневно с 8:00 до 20:00</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer как в index.php -->
    <footer class="footer">
        <div class="footer-content">
            <p>📞 +7 (995) 273-74-28</p>
            <p>✉️ vet.help@example.com</p>
            <p>🕒 Работаем: Круглосуточно</p>
            <p>&copy; <?= date('Y') ?> Поиск хвостиков. Все права защищены.</p>
        </div>
    </footer>

    <script>
        // Инициализация карты
        ymaps.ready(init);
        function init() {
            // Центрируем карту около Омска
            var map = new ymaps.Map("map", {
                center: [54.9893, 73.3686],
                zoom: 11,
                controls: ["zoomControl", "fullscreenControl"]
            });

            // Метки приютов с улучшенным дизайном
            var placemark1 = new ymaps.Placemark(
                [55.005847, 73.463146], {
                    balloonContent: `
                        <div style="font-family: Arial, sans-serif; max-width: 250px;">
                            <h4 style="color: #2c3e50; margin: 0 0 10px 0;">Муниципальный приют для животных "Друг"</h4>
                            <p style="margin: 5px 0;"><strong>📍 Адрес:</strong> 2-я Учхозная ул., 2А</p>
                            <p style="margin: 5px 0;"><strong>🕒 Режим:</strong> Ежедневно с 9:00 до 18:00</p>
                        </div>
                    `
                }, {
                    preset: 'islands#blueDotIcon'
                }
            );
            
            var placemark2 = new ymaps.Placemark(
                [54.909580, 73.306381], {
                    balloonContent: `
                        <div style="font-family: Arial, sans-serif; max-width: 250px;">
                            <h4 style="color: #2c3e50; margin: 0 0 10px 0;">Приют "Омские хвостики"</h4>
                            <p style="margin: 5px 0;"><strong>📍 Адрес:</strong> 1-я Казахстанская ул., 3/2</p>
                            <p style="margin: 5px 0;"><strong>📞 Телефон:</strong> +7 (3812) 49-87-66</p>
                            <p style="margin: 5px 0;"><strong>🕒 Режим:</strong> Пн-Пт: 10:00-17:00, Сб-Вс: 11:00-16:00</p>
                        </div>
                    `
                }, {
                    preset: 'islands#greenDotIcon'
                }
            );
            
            var placemark3 = new ymaps.Placemark(
                [54.974013, 73.409499], {
                    balloonContent: `
                        <div style="font-family: Arial, sans-serif; max-width: 250px;">
                            <h4 style="color: #2c3e50; margin: 0 0 10px 0;">Приют "Джульбарс и его друзья"</h4>
                            <p style="margin: 5px 0;"><strong>📍 Адрес:</strong> ул. Масленникова, 80</p>
                            <p style="margin: 5px 0;"><strong>🕒 Режим:</strong> Ежедневно с 8:00 до 20:00</p>
                        </div>
                    `
                }, {
                    preset: 'islands#redDotIcon'
                }
            );

            map.geoObjects.add(placemark1);
            map.geoObjects.add(placemark2);
            map.geoObjects.add(placemark3);
        }

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
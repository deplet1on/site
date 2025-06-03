<?php
// Генерация CSRF токена
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Получение отзывов из БД
$host = 'localhost';
$dbname = 'a1120713_11';
$username = 'a1120713_11';
$password = 'tumupiguzi';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ИСПРАВЛЕННЫЙ ЗАПРОС (название таблицы в нижнем регистре)
    $stmt = $pdo->query("SELECT * FROM reviews  ORDER BY created_at DESC LIMIT 10");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reviews = [];
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Поиск хвостиков</title>
    <!-- Подключение Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Подключение Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Подключение собственного CSS -->
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="vkladka.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LcWgE4rAAAAAMIyY6LngeWTDsCVqSFhJ7q0hvHl"></script>
    
    <!-- Дополнительные мета-теги для безопасности -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <style>
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
        
        .swiper-slide {
            height: auto !important;
        }
        
        /* Новые стили для расположения формы и FAQ */
        .form-faq-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .form-faq-container > section {
            flex: 1;
            min-width: 300px;
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
            background-color: #29B6F6;
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
        }
    </style>
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

<div class="centered-section">
    <!-- Секция с картой -->
    <section class="map-section">
        <h2>Карта найденных и пропавших животных</h2>
        <div id="map" style="height: 400px;">
            <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=a97f57d3-dc31-4396-ac2d-78d71b4aced3"
                    type="text/javascript"></script>
            <script>
                function init() {
                    // Создаем карту
                    const myMap = new ymaps.Map("map", {
                        center: [54.9876, 73.3972], // Омск
                        zoom: 12,
                        controls: ["zoomControl", "fullscreenControl"]
                    });

                    // Загрузка данных о найденных животных
                    fetch('db/get_found_pets.php')
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(pet => {
                                // Экранирование данных для предотвращения XSS
                                const name = pet.name ? pet.name.replace(/[<>]/g, '') : 'Неизвестно';
                                const species = pet.species ? pet.species.replace(/[<>]/g, '') : 'Неизвестно';
                                const description = pet.description ? pet.description.replace(/[<>]/g, '') : '';
                                
                                const photoHTML = pet.photo && pet.photo !== 'null'
                                    ? `<br><img src="${pet.photo}" alt="Фото животного" style="max-width:100px; margin-top:5px;" onerror="this.style.display='none'">`
                                    : '';

                                const placemark = new ymaps.Placemark(
                                    [parseFloat(pet.latitude), parseFloat(pet.longitude)],
                                    {
                                        balloonContent: `<b>${name} (${species})</b><br>${description}${photoHTML}`
                                    },
                                    {
                                        preset: 'islands#greenDotIcon'
                                    }
                                );
                                myMap.geoObjects.add(placemark);
                            });
                        })
                        .catch(error => console.error('Ошибка загрузки данных о найденных животных:', error));

                    // Загрузка данных о пропавших животных
                    fetch('db/get_lost_pets.php')
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(pet => {
                                // Экранирование данных для предотвращения XSS
                                const ownerName = pet.owner_name ? pet.owner_name.replace(/[<>]/g, '') : 'Неизвестно';
                                const animalType = pet.animal_type ? pet.animal_type.replace(/[<>]/g, '') : 'Неизвестно';
                                const description = pet.description ? pet.description.replace(/[<>]/g, '') : '';
                                
                                const photoHTML = pet.photo_path && pet.photo_path !== 'null'
                                    ? `<br><img src="${pet.photo_path}" alt="Фото животного" style="max-width:100px; margin-top:5px;" onerror="this.style.display='none'">`
                                    : '';

                                const placemark = new ymaps.Placemark(
                                    [parseFloat(pet.latitude), parseFloat(pet.longitude)],
                                    {
                                        balloonContent: `<b>${ownerName} (${animalType})</b><br>${description}${photoHTML}`
                                    },
                                    {
                                        preset: 'islands#redDotIcon'
                                    }
                                );
                                myMap.geoObjects.add(placemark);
                            });
                        })
                        .catch(error => console.error('Ошибка загрузки данных о пропавших животных:', error));
                }

                ymaps.ready(init);
            </script>
        </div>
    </section>
    
    <!-- Отзывы -->
    <section class="reviews">
        <h2>Отзывы о нас</h2>
        <?php if (count($reviews) > 0): ?>
            <div class="swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($reviews as $review): ?>
                        <div class="swiper-slide">
                            <?php if (!empty($review['photo']) && $review['photo'] !== 'null'): ?>
                                <img src="uploads/<?= htmlspecialchars($review['photo'], ENT_QUOTES, 'UTF-8') ?>" 
                                     class="review-image" 
                                     alt="Фото к отзыву"
                                     loading="lazy"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            <h3><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?= htmlspecialchars($review['review'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="rating">
                                <?php for ($i = 0; $i < min(5, max(0, (int)$review['rating'])); $i++): ?>
                                    <i class="bi bi-star-fill"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
        <?php else: ?>
            <p class="text-center">Пока нет отзывов. Будьте первым!</p>
        <?php endif; ?>
    </section>


    <div class="form-faq-container">
        <section class="add-review">
            <h2>Оставить отзыв</h2>
            
            <?php if (isset($_GET['review_success'])): ?>
                <div class="alert alert-success">Спасибо за ваш отзыв! Он будет опубликован после модерации.</div>
            <?php endif; ?>
            
            <form method="POST" action="db/submit_review.php" enctype="multipart/form-data" class="review-form" id="reviewForm">
                <!-- CSRF защита -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <!-- reCAPTCHA token будет добавлен сюда динамически -->
                <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                
                <div class="form-group">
                    <label for="reviewer-name">Ваше имя:</label>
                    <input type="text" id="reviewer-name" name="name" required 
                           placeholder="Введите ваше имя" maxlength="50" minlength="2">
                </div>

                <div class="form-group">
                    <label for="review-text">Текст отзыва:</label>
                    <textarea id="review-text" name="review" rows="4" required 
                              placeholder="Напишите ваш отзыв" maxlength="1000" minlength="10"></textarea>
                    <small class="form-text text-muted">От 10 до 1000 символов</small>
                </div>

                <div class="form-group">
                    <label for="photo">Прикрепить фотографию (до 10 МБ):</label>
                    <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small class="form-text text-muted">Разрешены форматы: JPG, PNG, GIF, WebP</small>
                </div>

                <div class="form-group">
                    <label for="review-rating">Оценка:</label>
                    <select id="review-rating" name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                        <option value="4">⭐⭐⭐⭐ (4)</option>
                        <option value="3">⭐⭐⭐ (3)</option>
                        <option value="2">⭐⭐ (2)</option>
                        <option value="1">⭐ (1)</option>
                    </select>
                </div>
                
                <!-- ЗАМЕНЕННЫЙ БЛОК СОГЛАСИЯ НА ОБРАБОТКУ ДАННЫХ -->
                <div class="privacy-toggle">
                    <label class="toggle-switch">
                        <input type="checkbox" name="privacy" id="privacy" required>
                        <span class="slider round"></span>
                    </label>
                    <label for="privacy" class="toggle-label">
                        <a href="/fz.php" target="_blank">Я согласен(на) на обработку персональных данных</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" id="submitBtn">Отправить отзыв</button>
            </form>
        </section>

        <section class="faq-section">
            <div class="faq-wrapper">
                <h2>Частые вопросы</h2>
                <div class="faq-container">
                    <div class="faq-item">
                        <button class="faq-question">Как добавить пропавшее животное?</button>
                        <div class="faq-answer">
                            <p>Чтобы добавить пропавшее животное, перейдите на страницу "Добавить пропавшее животное" и
                                заполните форму.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">Как найти своего питомца?</button>
                        <div class="faq-answer">
                            <p>Используйте карту на сайте, чтобы найти объявления о найденных животных в вашем районе.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">Как сообщить о найденном животном?</button>
                        <div class="faq-answer">
                            <p>Перейдите в раздел "Сообщить о найденном животном", заполните форму с информацией (фотография, вид, описание, email, адрес, дату) и отправьте её. Ваше объявление появится на сайте и в базе данных для поиска.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">Сколько времени занимает публикация объявления?</button>
                        <div class="faq-answer">
                            <p>Объявление публикуется мгновенно после отправки формы. Проверка модератором не требуется.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <button class="faq-question">Можно ли добавить фото питомца?</button>
                        <div class="faq-answer">
                            <p>Да, при добавлении объявления вы можете загрузить фотографию животного. Это значительно повышает шансы на его обнаружение.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script>
    // Инициализация reCAPTCHA v3
    grecaptcha.ready(function() {
        console.log('reCAPTCHA готова к работе');
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Инициализация Swiper для отзывов
        const swiper = new Swiper('.swiper', {
            direction: 'horizontal',
            slidesPerView: 3,
            spaceBetween: 30,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 30
                }
            }
        });

        // FAQ функционал
        const faqQuestions = document.querySelectorAll('.faq-question');
        faqQuestions.forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                document.querySelectorAll('.faq-answer').forEach(item => {
                    if (item !== answer) {
                        item.classList.remove('open');
                    }
                });
                answer.classList.toggle('open');
            });
        });

        // Валидация формы отзыва с reCAPTCHA
        const reviewForm = document.getElementById('reviewForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Предотвращаем стандартную отправку
                
                const name = document.getElementById('reviewer-name').value.trim();
                const review = document.getElementById('review-text').value.trim();
                const photo = document.getElementById('photo').files[0];
                
                // Проверка длины имени
                if (name.length < 2 || name.length > 50) {
                    alert('Имя должно содержать от 2 до 50 символов');
                    return;
                }
                
                // Проверка длины отзыва
                if (review.length < 10 || review.length > 1000) {
                    alert('Отзыв должен содержать от 10 до 1000 символов');
                    return;
                }
                
                // Проверка размера файла
                if (photo && photo.size > 10 * 1024 * 1024) {
                    alert('Размер файла не должен превышать 10 МБ');
                    return;
                }
                
                // Проверка типа файла
                if (photo) {
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!allowedTypes.includes(photo.type)) {
                        alert('Разрешены только изображения в форматах JPG, PNG, GIF, WebP');
                        return;
                    }
                }
                
                // Отключение кнопки для предотвращения повторной отправки
                submitBtn.disabled = true;
                submitBtn.textContent = 'Отправляется...';
                
                // Выполнение reCAPTCHA и отправка формы
                grecaptcha.ready(function() {
                    grecaptcha.execute('6LcWgE4rAAAAAMIyY6LngeWTDsCVqSFhJ7q0hvHl', {action: 'submit_review'}).then(function(token) {
                        // Добавляем токен в скрытое поле
                        document.getElementById('recaptcha_token').value = token;
                        
                        // Отправляем форму
                        reviewForm.submit();
                    }).catch(function(error) {
                        console.error('Ошибка reCAPTCHA:', error);
                        alert('Ошибка проверки безопасности. Попробуйте еще раз.');
                        
                        // Возвращаем кнопку в исходное состояние
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Отправить отзыв';
                    });
                });
            });
        }

        // Счетчик символов для отзыва
        const reviewTextarea = document.getElementById('review-text');
        if (reviewTextarea) {
            const counterDiv = document.createElement('div');
            counterDiv.className = 'character-counter';
            reviewTextarea.parentNode.appendChild(counterDiv);
            
            reviewTextarea.addEventListener('input', function() {
                const length = this.value.length;
                counterDiv.textContent = `${length}/1000 символов`;
                counterDiv.style.color = length > 1000 ? 'red' : '#666';
            });
        }
        
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
<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <p>📞 +7 (995) 273-74-28</p>
        <p>✉️ vet.help@example.com</p>
        <p>🕒 Работаем: Круглосуточно</p>
        <p>&copy; <?= date('Y') ?> Поиск хвостиков. Все права защищены.</p>
    </div>
</footer>
</body>
</html>
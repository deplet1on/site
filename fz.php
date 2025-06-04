<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="vkladka.png" />
    
    <style>
        /* Адаптивное меню */
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
            right: 15px;
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
            white-space: nowrap;
        }
        
        .main-nav a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .centered-section {
            max-width: 1200px;
            margin: 0px auto;
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 8px;
            color: white;
            line-height: 1.8;
        }
        
        .privacy-content {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .privacy-content h2 {
            color: #29B6F6;
            margin-top: 30px;
            border-bottom: 1px solid #29B6F6;
            padding-bottom: 10px;
        }
        
        .privacy-content h3 {
            color: #29B6F6;
            margin-top: 20px;
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
            
            .centered-section {
                padding: 15px;
            }
            
            .privacy-content {
                padding: 20px;
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
            </ul>
        </nav>
    </div>
</header>

<div class="centered-section">
    <h1 class="text-center mb-4">Согласие на обработку персональных данных</h1>
    
    <div class="privacy-content">
        <p>Оставляя на сайте "Поиск хвостиков" (далее — Сайт) любые данные, которые относятся к персональным данным в соответствии с Федеральным законом от 27.07.2006 №152-ФЗ "О персональных данных", путем заполнения полей форм на Сайте, вы:</p>
        
        <ol>
            <li>Подтверждаете, что все указанные данные принадлежат лично вам</li>
            <li>Подтверждаете, что внимательно ознакомились с настоящим Соглашением</li>
            <li>Даете согласие на обработку персональных данных администрацией Сайта</li>
            <li>Выражаете согласие с условиями обработки персональных данных без оговорок и ограничений</li>
        </ol>
        
        <h2>1. Перечень данных</h2>
        <p>Вы даете согласие на обработку следующих персональных данных:</p>
        <ul>
            <li>ФИО (при наличии)</li>
            <li>Контактный телефон</li>
            <li>Адрес электронной почты (email)</li>
            <li>Адрес места нахождения</li>
            <li>Фотографии животных</li>
            <li>Любые другие данные, добровольно предоставленные вами</li>
        </ul>
        
        <h2>2. Цели обработки</h2>
        <p>Обработка персональных данных осуществляется в следующих целях:</p>
        <ul>
            <li>Оказание услуг по поиску пропавших животных</li>
            <li>Публикация объявлений о пропавших/найденных животных</li>
            <li>Обратная связь с пользователями</li>
            <li>Информирование о статусе объявлений</li>
            <li>Улучшение качества предоставляемых услуг</li>
        </ul>
        
        <h2>3. Права администрации сайта</h2>
        <p>Администрация Сайта вправе осуществлять следующие действия с персональными данными:</p>
        <ul>
            <li>Сбор и систематизация</li>
            <li>Хранение в течение сроков, установленных законодательством</li>
            <li>Уточнение (обновление, изменение)</li>
            <li>Использование исключительно для заявленных целей</li>
            <li>Уничтожение или обезличивание</li>
            <li>Передача по требованию уполномоченных государственных органов</li>
        </ul>
        
        <h2>4. Срок действия согласия</h2>
        <p>Настоящее согласие действует бессрочно с момента предоставления данных. Вы вправе отозвать согласие, направив письменное заявление по адресу: vet.help@example.com</p>
        
        <h2>5. Конфиденциальность</h2>
        <p>Администрация Сайта обязуется принимать все необходимые меры для защиты персональных данных от несанкционированного доступа или раскрытия.</p>
        
        <h2>6. Изменения в соглашении</h2>
        <p>Администрация Сайта оставляет за собой право вносить изменения в настоящее Соглашение. Актуальная версия всегда доступна на этой странице.</p>
        
        <p class="mt-4"><strong>Дата последнего обновления:</strong> 02 июня 2025 г.</p>
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
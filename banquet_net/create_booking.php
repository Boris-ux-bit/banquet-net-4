<?php
session_start();
require_once 'config.php';

$page_title = 'Создание заявки';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = false;

// Получаем список помещений для выпадающего списка
$stmt = $pdo->query("SELECT * FROM rooms ORDER BY type, name");
$rooms = $stmt->fetchAll();

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'] ?? '';
    $event_date = trim($_POST['event_date'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Валидация
    if (empty($room_id)) {
        $error = 'Выберите помещение';
    } elseif (empty($event_date)) {
        $error = 'Укажите дату банкета';
    } elseif (empty($payment_method)) {
        $error = 'Выберите способ оплаты';
    } else {
        // Проверка формата даты (ДД.ММ.ГГГГ)
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $event_date)) {
            $parts = explode('.', $event_date);
            $event_date_db = "$parts[2]-$parts[1]-$parts[0]";
            
            // Проверка, что дата не в прошлом
            $today = date('Y-m-d');
            if ($event_date_db < $today) {
                $error = 'Дата банкета не может быть в прошлом';
            } else {
                // Сохраняем заявку
                $sql = "INSERT INTO bookings (user_id, room_id, event_date, payment_method, status) 
                        VALUES (?, ?, ?, ?, 'Новая')";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$user_id, $room_id, $event_date_db, $payment_method])) {
                    $success = true;
                } else {
                    $error = 'Ошибка при создании заявки';
                }
            }
        } else {
            $error = 'Неверный формат даты. Используйте ДД.ММ.ГГГГ';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Банкетам.Нет - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Дополнительные стили для страницы создания заявки */
        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .form-header h2 {
            color: #333;
            font-size: 24px;
        }
        
        .form-header p {
            color: #666;
            font-size: 14px;
        }
        
        /* Анимация для карточек помещений */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group {
            animation: fadeInUp 0.4s ease forwards;
            opacity: 0;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.05s; }
        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .form-group:nth-child(3) { animation-delay: 0.15s; }
        
        .btn {
            animation: fadeInUp 0.4s ease forwards;
            animation-delay: 0.2s;
            opacity: 0;
        }
        
        .nav-links {
            animation: fadeInUp 0.4s ease forwards;
            animation-delay: 0.25s;
            opacity: 0;
        }
        
        /* Сообщение об успехе */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            animation: fadeInUp 0.4s ease;
        }
        
        .success-message h3 {
            margin-bottom: 10px;
        }
        
        /* Стили для выпадающего списка */
        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }
        
        /* Подсказка по формату даты */
        .date-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        /* Поле даты с календарём */
        input[type="text"] {
            font-family: monospace;
            letter-spacing: 1px;
        }
        
        /* Информационная карточка */
        .info-card {
            background: linear-gradient(135deg, #667eea15, #764ba215);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #667eea30;
        }
        
        .info-card p {
            margin: 5px 0;
            color: #555;
        }
        
        /* Анимация ошибки */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            animation: shake 0.4s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        /* Эффект при наведении на select option */
        select option {
            padding: 10px;
        }
        
        /* Адаптив */
        @media (max-width: 480px) {
            .form-header h2 {
                font-size: 20px;
            }
        }
        
        /* Анимация для кнопки */
        .btn {
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        
        .btn:active::after {
            width: 200px;
            height: 200px;
        }
        
        /* Стили для спиннера загрузки */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-loading .btn-text {
            visibility: hidden;
        }
        
        .btn-loading::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            z-index: 2;
        }
        
        .btn-loading::after {
            display: none;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Банкетам.Нет</h1>
            <p>Создайте заявку на бронирование</p>
        </div>
        
        <div class="content">
            <?php if($success): ?>
                <div class="success-message">
                    <h3>✅ Заявка успешно создана!</h3>
                    <p>Ваша заявка отправлена на согласование администратору.</p>
                    <p>Статус заявки можно отслеживать в <strong>личном кабинете</strong>.</p>
                    <br>
                    <a href="profile.php" class="btn" style="display: inline-block; width: auto; padding: 10px 20px;">📋 Перейти в личный кабинет</a>
                    <a href="create_booking.php" class="btn btn-secondary" style="display: inline-block; width: auto; padding: 10px 20px; margin-top: 10px;">➕ Создать ещё одну</a>
                </div>
            <?php else: ?>
                <div class="form-header">
                    <h2>📅 Новая заявка на банкет</h2>
                    <p>Заполните форму ниже, чтобы забронировать помещение</p>
                </div>
                
                <div class="info-card">
                    <p>💡 <strong>Совет:</strong> Чем раньше вы забронируете зал, тем больше выбор!</p>
                    <p>📞 По всем вопросам звоните: +7 (XXX) XXX-XX-XX</p>
                </div>
                
                <?php if($error): ?>
                    <div class="error-message">
                        <span>❌</span> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label>🏛️ Выберите помещение</label>
                        <select name="room_id" required>
                            <option value="">-- Выберите зал или веранду --</option>
                            <?php foreach($rooms as $room): ?>
                                <option value="<?php echo $room['id']; ?>" 
                                    <?php echo (isset($_POST['room_id']) && $_POST['room_id'] == $room['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['name']); ?> 
                                    (<?php echo htmlspecialchars($room['type']); ?>, 
                                    вместимость: <?php echo $room['capacity']; ?> чел., 
                                    от <?php echo number_format($room['price_per_hour'], 0, '', ' '); ?> ₽/час)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>📆 Дата банкета</label>
                        <input type="text" 
                               name="event_date" 
                               id="event_date"
                               placeholder="ДД.ММ.ГГГГ" 
                               value="<?php echo htmlspecialchars($_POST['event_date'] ?? ''); ?>"
                               required
                               maxlength="10"
                               autocomplete="off">
                        <div class="date-hint">
                            📅 Формат: ДД.ММ.ГГГГ (например, 25.12.2024)
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>💳 Способ оплаты</label>
                        <select name="payment_method" required>
                            <option value="">-- Выберите способ оплаты --</option>
                            <option value="Наличные" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Наличные') ? 'selected' : ''; ?>>💰 Наличные</option>
                            <option value="Карта" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Карта') ? 'selected' : ''; ?>>💳 Банковская карта</option>
                            <option value="Безналичный расчёт" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'Безналичный расчёт') ? 'selected' : ''; ?>>🏦 Безналичный расчёт</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn" id="submitBtn">
                        <span class="btn-text">🎉 Создать заявку</span>
                    </button>
                    
                    <div class="nav-links">
                        <a href="profile.php">← Вернуться в личный кабинет</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/js/slider.js"></script>
    <script>
        // Маска для ввода даты (ДД.ММ.ГГГГ)
        const dateInput = document.getElementById('event_date');
        if (dateInput) {
            dateInput.addEventListener('input', function(e) {
                let value = this.value.replace(/[^\d]/g, '');
                if (value.length >= 2 && value.length < 5) {
                    value = value.slice(0, 2) + '.' + value.slice(2);
                } else if (value.length >= 5 && value.length < 9) {
                    value = value.slice(0, 2) + '.' + value.slice(2, 4) + '.' + value.slice(4, 8);
                } else if (value.length >= 9) {
                    value = value.slice(0, 2) + '.' + value.slice(2, 4) + '.' + value.slice(4, 8);
                }
                this.value = value;
            });
            
            // Автоматическая фокусировка на поле даты
            dateInput.addEventListener('focus', function() {
                if (this.value === '') {
                    this.placeholder = 'ДД.ММ.ГГГГ';
                }
            });
        }
        
        // Анимация загрузки при отправке формы
        const form = document.getElementById('bookingForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const btn = document.getElementById('submitBtn');
                const btnText = btn.querySelector('.btn-text');
                
                // Меняем текст и добавляем класс загрузки
                btnText.innerHTML = '⏳ Создание заявки...';
                btn.classList.add('btn-loading');
                btn.disabled = true;
                
                // Если через 10 секунд форма не отправилась (ошибка сети), восстанавливаем
                setTimeout(() => {
                    if (btn.disabled) {
                        btnText.innerHTML = '🎉 Создать заявку';
                        btn.classList.remove('btn-loading');
                        btn.disabled = false;
                    }
                }, 10000);
            });
        }
        
        // Валидация перед отправкой
        if (form) {
            form.addEventListener('submit', function(e) {
                const roomSelect = document.querySelector('select[name="room_id"]');
                const dateValue = document.getElementById('event_date').value;
                const paymentSelect = document.querySelector('select[name="payment_method"]');
                
                if (!roomSelect.value) {
                    e.preventDefault();
                    showError('Выберите помещение');
                    return false;
                }
                
                if (!dateValue) {
                    e.preventDefault();
                    showError('Укажите дату банкета');
                    return false;
                }
                
                // Проверка формата даты
                const dateRegex = /^\d{2}\.\d{2}\.\d{4}$/;
                if (!dateRegex.test(dateValue)) {
                    e.preventDefault();
                    showError('Неверный формат даты. Используйте ДД.ММ.ГГГГ');
                    return false;
                }
                
                // Проверка, что дата не в прошлом
                const parts = dateValue.split('.');
                const inputDate = new Date(parts[2], parts[1] - 1, parts[0]);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (inputDate < today) {
                    e.preventDefault();
                    showError('Дата банкета не может быть в прошлом');
                    return false;
                }
                
                if (!paymentSelect.value) {
                    e.preventDefault();
                    showError('Выберите способ оплаты');
                    return false;
                }
                
                return true;
            });
        }
        
        // Функция показа ошибки
        function showError(message) {
            // Удаляем старую ошибку, если есть
            const oldError = document.querySelector('.error-message');
            if (oldError) oldError.remove();
            
            // Создаём новую ошибку
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = '<span>❌</span> ' + message;
            
            // Вставляем перед формой
            const formElement = document.getElementById('bookingForm');
            formElement.parentNode.insertBefore(errorDiv, formElement);
            
            // Прокручиваем к ошибке
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Анимация тряски
            errorDiv.style.animation = 'shake 0.4s ease';
            
            // Через 5 секунд удаляем ошибку
            setTimeout(() => {
                if (errorDiv) errorDiv.remove();
            }, 5000);
        }
        
        // Эффект при наведении на кнопку
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 25px rgba(102,126,234,0.4)';
            });
            
            submitBtn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }
        
        // Подсказка для поля даты при наведении
        if (dateInput) {
            dateInput.addEventListener('mouseenter', function() {
                this.style.borderColor = '#667eea';
            });
            
            dateInput.addEventListener('mouseleave', function() {
                this.style.borderColor = '#e0e0e0';
            });
        }
    </script>
</body>
</html>
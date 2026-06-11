<?php
session_start();
require_once 'config.php';

$page_title = 'Регистрация';

$errors = [];
$success = false;
$form_data = [];

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $form_data['login'] = trim($_POST['login'] ?? '');
    $form_data['password'] = $_POST['password'] ?? '';
    $form_data['full_name'] = trim($_POST['full_name'] ?? '');
    $form_data['phone'] = trim($_POST['phone'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    
    // ===== ВАЛИДАЦИЯ =====
    
    // 1. Проверка логина (латиница + цифры, минимум 6 символов)
    if (empty($form_data['login'])) {
        $errors['login'] = 'Введите логин';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $form_data['login'])) {
        $errors['login'] = 'Логин должен содержать минимум 6 символов (только латиница и цифры)';
    } else {
        // Проверка уникальности логина
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$form_data['login']]);
        if ($stmt->fetch()) {
            $errors['login'] = 'Такой логин уже существует';
        }
    }
    
    // 2. Проверка пароля (минимум 8 символов)
    if (empty($form_data['password'])) {
        $errors['password'] = 'Введите пароль';
    } elseif (strlen($form_data['password']) < 8) {
        $errors['password'] = 'Пароль должен быть минимум 8 символов';
    }
    
    // 3. Проверка ФИО
    if (empty($form_data['full_name'])) {
        $errors['full_name'] = 'Введите ФИО';
    } elseif (strlen($form_data['full_name']) < 2) {
        $errors['full_name'] = 'ФИО должно содержать минимум 2 символа';
    }
    
    // 4. Проверка телефона
    if (empty($form_data['phone'])) {
        $errors['phone'] = 'Введите номер телефона';
    } elseif (strlen($form_data['phone']) < 5) {
        $errors['phone'] = 'Введите корректный номер телефона';
    }
    
    // 5. Проверка email
    if (empty($form_data['email'])) {
        $errors['email'] = 'Введите email';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    } else {
        // Проверка уникальности email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$form_data['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Этот email уже зарегистрирован';
        }
    }
    
    // ===== СОХРАНЕНИЕ В БД =====
    if (empty($errors)) {
        try {
            // Хешируем пароль
            $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
            
            // Подготавливаем запрос
            $sql = "INSERT INTO users (login, password, full_name, phone, email, role) 
                    VALUES (?, ?, ?, ?, ?, 'user')";
            $stmt = $pdo->prepare($sql);
            
            // Выполняем запрос
            if ($stmt->execute([
                $form_data['login'],
                $hashed_password,
                $form_data['full_name'],
                $form_data['phone'],
                $form_data['email']
            ])) {
                $success = true;
                // Очищаем данные формы после успешной регистрации
                $form_data = [];
            } else {
                $errors['general'] = 'Ошибка при сохранении данных. Попробуйте ещё раз.';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Банкетам.Нет - Регистрация</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            animation: fadeInUp 0.4s ease;
        }
        
        .success-message h3 {
            margin-bottom: 10px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            animation: shake 0.4s ease;
        }
        
        .field-error {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }
        
        input.error-input {
            border-color: #e74c3c;
            background-color: #fff8f8;
        }
        
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
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .form-group {
            animation: fadeInUp 0.4s ease forwards;
            opacity: 0;
        }
        
        .form-group:nth-child(1) { animation-delay: 0.05s; }
        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .form-group:nth-child(3) { animation-delay: 0.15s; }
        .form-group:nth-child(4) { animation-delay: 0.2s; }
        .form-group:nth-child(5) { animation-delay: 0.25s; }
        
        .btn {
            animation: fadeInUp 0.4s ease forwards;
            animation-delay: 0.3s;
            opacity: 0;
        }
        
        .nav-links {
            animation: fadeInUp 0.4s ease forwards;
            animation-delay: 0.35s;
            opacity: 0;
        }
        
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
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Банкетам.Нет</h1>
            <p>Создайте аккаунт для бронирования</p>
        </div>
        
        <div class="content">
            <?php if($success): ?>
                <div class="success-message">
                    <h3>✅ Регистрация успешна!</h3>
                    <p>Добро пожаловать, <?php echo htmlspecialchars($form_data['login'] ?? 'пользователь'); ?>!</p>
                    <p>Теперь вы можете войти в систему.</p>
                    <br>
                    <a href="login.php" class="btn" style="display: inline-block; width: auto; padding: 10px 30px;">🚪 Войти в систему</a>
                </div>
            <?php else: ?>
                <h2 style="text-align: center; margin-bottom: 20px;">📝 Регистрация нового пользователя</h2>
                
                <?php if(isset($errors['general'])): ?>
                    <div class="error-message">
                        ❌ <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="form-group">
                        <label>👤 Логин *</label>
                        <input type="text" 
                               name="login" 
                               id="login"
                               value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>" 
                               placeholder="Только латиница и цифры, мин. 6 символов"
                               class="<?php echo isset($errors['login']) ? 'error-input' : ''; ?>"
                               required>
                        <?php if(isset($errors['login'])): ?>
                            <div class="field-error">❌ <?php echo $errors['login']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>🔒 Пароль *</label>
                        <div class="password-wrapper">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="Минимум 8 символов"
                                   class="<?php echo isset($errors['password']) ? 'error-input' : ''; ?>"
                                   required>
                            <span class="toggle-password" onclick="togglePassword()">👁️</span>
                        </div>
                        <div class="password-hint">💡 Пароль должен содержать минимум 8 символов</div>
                        <?php if(isset($errors['password'])): ?>
                            <div class="field-error">❌ <?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>👨‍💼 ФИО *</label>
                        <input type="text" 
                               name="full_name" 
                               value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>" 
                               placeholder="Ваше полное имя"
                               class="<?php echo isset($errors['full_name']) ? 'error-input' : ''; ?>"
                               required>
                        <?php if(isset($errors['full_name'])): ?>
                            <div class="field-error">❌ <?php echo $errors['full_name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>📞 Телефон *</label>
                        <input type="tel" 
                               name="phone" 
                               value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" 
                               placeholder="+7 (XXX) XXX-XX-XX"
                               class="<?php echo isset($errors['phone']) ? 'error-input' : ''; ?>"
                               required>
                        <?php if(isset($errors['phone'])): ?>
                            <div class="field-error">❌ <?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>📧 Email *</label>
                        <input type="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                               placeholder="example@mail.com"
                               class="<?php echo isset($errors['email']) ? 'error-input' : ''; ?>"
                               required>
                        <?php if(isset($errors['email'])): ?>
                            <div class="field-error">❌ <?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn" id="submitBtn">
                        <span class="btn-text">📝 Зарегистрироваться</span>
                    </button>
                    
                    <div class="nav-links">
                        <a href="login.php">🔑 Уже есть аккаунт? Войти</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Переключение видимости пароля
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }
        
        // Валидация перед отправкой
        const form = document.getElementById('registerForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const login = document.querySelector('input[name="login"]').value;
                const password = document.querySelector('input[name="password"]').value;
                const fullName = document.querySelector('input[name="full_name"]').value;
                const phone = document.querySelector('input[name="phone"]').value;
                const email = document.querySelector('input[name="email"]').value;
                
                // Проверка логина
                const loginRegex = /^[a-zA-Z0-9]{6,}$/;
                if (!loginRegex.test(login)) {
                    e.preventDefault();
                    showFieldError('login', 'Логин должен содержать минимум 6 символов (только латиница и цифры)');
                    return false;
                }
                
                // Проверка пароля
                if (password.length < 8) {
                    e.preventDefault();
                    showFieldError('password', 'Пароль должен быть минимум 8 символов');
                    return false;
                }
                
                // Проверка ФИО
                if (fullName.length < 2) {
                    e.preventDefault();
                    showFieldError('full_name', 'Введите ФИО');
                    return false;
                }
                
                // Проверка телефона
                if (phone.length < 5) {
                    e.preventDefault();
                    showFieldError('phone', 'Введите номер телефона');
                    return false;
                }
                
                // Проверка email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    showFieldError('email', 'Введите корректный email адрес');
                    return false;
                }
                
                // Анимация кнопки
                const btn = document.getElementById('submitBtn');
                const btnText = btn.querySelector('.btn-text');
                btnText.innerHTML = '⏳ Регистрация...';
                btn.classList.add('btn-loading');
                btn.disabled = true;
            });
        }
        
        function showFieldError(fieldName, message) {
            const field = document.querySelector(`input[name="${fieldName}"]`);
            if (field) {
                field.classList.add('error-input');
                
                // Удаляем старую ошибку
                const oldError = field.parentElement.querySelector('.field-error');
                if (oldError) oldError.remove();
                
                // Добавляем новую ошибку
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.innerHTML = '❌ ' + message;
                field.parentElement.appendChild(errorDiv);
                
                // Прокручиваем к полю
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Анимация тряски
                field.style.animation = 'shake 0.3s ease';
                setTimeout(() => {
                    field.style.animation = '';
                }, 300);
            }
        }
        
        // Убираем подсветку ошибки при вводе
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error-input');
                const errorDiv = this.parentElement.querySelector('.field-error');
                if (errorDiv) errorDiv.remove();
            });
        });
    </script>
</body>
</html>

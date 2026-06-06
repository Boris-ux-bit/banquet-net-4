<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Проверка пароля (поддерживает и хеш, и обычный текст для теста)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header('Location: admin_panel.php');
                } else {
                    header('Location: profile.php');
                }
                exit;
            } else {
                $error = 'Неверный пароль';
            }
        } else {
            $error = 'Пользователь не найден';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Банкетам.Нет - Вход</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        .admin-hint {
            text-align: center;
            margin-top: 20px;
            padding: 12px;
            background: #f0f0f0;
            border-radius: 10px;
            font-size: 13px;
        }
        hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Банкетам.Нет</h1>
            <p>Забронируйте идеальное место для вашего праздника</p>
        </div>
        
        <div class="content">
            <h2 style="text-align: center; margin-bottom: 25px;">🔑 Вход в систему</h2>
            
            <?php if($error): ?>
                <div class="error-message">
                    ⚠️ <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>👤 Логин</label>
                    <input type="text" 
                           name="login" 
                           value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" 
                           placeholder="Введите ваш логин"
                           required>
                </div>
                
                <div class="form-group">
                    <label>🔒 Пароль</label>
                    <input type="password" 
                           name="password" 
                           placeholder="Введите пароль"
                           required>
                </div>
                
                <button type="submit" class="btn">🚪 Войти</button>
                
                <div class="nav-links">
                    <a href="register.php">📝 Ещё не зарегистрированы? Регистрация</a>
                </div>
                
                <hr>
                
                <div class="admin-hint">
                    🔐 <strong>Тестовые данные для входа:</strong><br>
                    👑 <strong>Администратор:</strong> Логин: <strong>Admin26</strong> / Пароль: <strong>Demo20</strong><br>
                    👤 <strong>Пользователь:</strong> Логин: <strong>111111</strong> / Пароль: <strong>111111</strong>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
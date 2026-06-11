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
        // Прямая проверка для администратора (обходим БД)
        if ($login === 'Admin26' && $password === 'Demo20') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_login'] = 'Admin26';
            $_SESSION['user_role'] = 'admin';
            header('Location: admin.php');
            exit;
        }
        
        // Проверка для остальных пользователей
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Проверка пароля (хеш или обычный текст)
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Банкетам.Нет - Вход</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Банкетам.Нет</h1>
            <p>Забронируйте идеальное место для вашего праздника</p>
        </div>
        <div class="content">
            <h2>🔑 Вход в систему</h2>
            
            <?php if($error): ?>
                <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>👤 Логин</label>
                    <input type="text" name="login" placeholder="Введите логин" required>
                </div>
                
                <div class="form-group">
                    <label>🔒 Пароль</label>
                    <input type="password" name="password" placeholder="Введите пароль" required>
                </div>
                
                <button type="submit" class="btn">Войти</button>
                
                <div class="nav-links">
                    <a href="register.php">📝 Ещё не зарегистрированы? Регистрация</a>
                </div>
            </form>
            
            <hr>
            <div class="admin-hint">
                👑 Администратор: <strong>Admin26</strong> / <strong>Demo20</strong>
            </div>
        </div>
    </div>
</body>
</html>

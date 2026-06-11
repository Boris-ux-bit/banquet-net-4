<?php
session_start();
require_once 'config.php';

// Проверка авторизации администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Обработка смены статуса
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action == 'assign') {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Банкет назначен' WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Заявка #$id назначена";
    } elseif ($action == 'complete') {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'Банкет завершен' WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Заявка #$id завершена";
    }
}

// Получаем все заявки
$stmt = $pdo->query("
    SELECT b.*, u.login, u.full_name, r.name as room_name, r.type as room_type
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Банкетам.Нет - Админ-панель</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        h1 { color: #667eea; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th { background: #f8f9fa; }
        .btn {
            display: inline-block;
            padding: 5px 10px;
            margin: 2px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
            font-size: 12px;
        }
        .btn-assign { background: #17a2b8; }
        .btn-complete { background: #28a745; }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .msg {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-new { background: #ffc107; }
        .status-assigned { background: #17a2b8; color: white; }
        .status-completed { background: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>👑 Панель администратора</h1>
        <p>Управление заявками на банкеты</p>
        
        <?php if(isset($msg)): ?>
            <div class="msg">✅ <?= $msg ?></div>
        <?php endif; ?>
        
        <?php if(count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Пользователь</th><th>Помещение</th><th>Дата</th><th>Оплата</th><th>Статус</th><th>Действия</th></tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $b): ?>
                        <?php
                            $status_class = '';
                            if ($b['status'] == 'Новая') $status_class = 'status-new';
                            elseif ($b['status'] == 'Банкет назначен') $status_class = 'status-assigned';
                            elseif ($b['status'] == 'Банкет завершен') $status_class = 'status-completed';
                        ?>
                        <tr>
                            <td><?= $b['id'] ?></td>
                            <td><?= htmlspecialchars($b['login']) ?><br><small><?= htmlspecialchars($b['full_name']) ?></small></td>
                            <td><?= htmlspecialchars($b['room_name']) ?><br><small><?= $b['room_type'] ?></small></td>
                            <td><?= date('d.m.Y', strtotime($b['event_date'])) ?></td>
                            <td><?= htmlspecialchars($b['payment_method']) ?></td>
                            <td><span class="status <?= $status_class ?>"><?= $b['status'] ?></span></td>
                            <td>
                                <?php if($b['status'] == 'Новая'): ?>
                                    <a href="?action=assign&id=<?= $b['id'] ?>" class="btn btn-assign" onclick="return confirm('Назначить банкет?')">📅 Назначить</a>
                                <?php endif; ?>
                                <?php if($b['status'] == 'Банкет назначен'): ?>
                                    <a href="?action=complete&id=<?= $b['id'] ?>" class="btn btn-complete" onclick="return confirm('Завершить банкет?')">✅ Завершить</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>📭 Нет заявок</p>
        <?php endif; ?>
        
        <br>
        <a href="logout.php" class="logout-btn">🚪 Выйти</a>
    </div>
</body>
</html>

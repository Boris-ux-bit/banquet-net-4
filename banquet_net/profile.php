<?php
// ========== ВСЯ PHP ЛОГИКА В САМОМ НАЧАЛЕ ==========
session_start();
require_once 'config.php';

$page_title = 'Личный кабинет';  // <-- ЭТО ДЛЯ ЗАГОЛОВКА ВКЛАДКИ

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем заявки пользователя
$stmt = $pdo->prepare("
    SELECT b.*, r.name as room_name,
    (SELECT COUNT(*) FROM reviews WHERE booking_id = b.id) as has_review
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// ========== PHP ЛОГИКА ЗАКОНЧЕНА, ДАЛЬШЕ НАЧИНАЕТСЯ HTML ==========
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Банкетам.Нет - <?php echo $page_title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Банкетам.Нет</h1>
            <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['user_login']) ?>!</p>
        </div>
        
        <div class="content">
            
            <!-- ========== ЭТО МЕСТО ДЛЯ СЛАЙДЕРА ========== -->
            <!-- 🔴 ВОТ СЮДА ВСТАВЬТЕ ЭТУ СТРОКУ 🔴 -->
            <div id="banquet-slider" style="margin-bottom: 30px;"></div>
            <!-- ========== СЛАЙДЕР ЗАКОНЧИЛСЯ ========== -->
            
            <h2>📋 История моих заявок</h2>
            
            <?php if(empty($bookings)): ?>
                <p style="text-align:center; color:#999;">У вас пока нет заявок. Создайте первую!</p>
            <?php else: ?>
                <div class="table-wrapper">
                    <table border="1" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background:#f0f0f0;">
                                <th>Помещение</th>
                                <th>Дата банкета</th>
                                <th>Способ оплаты</th>
                                <th>Статус</th>
                                <th>Отзыв</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['room_name']) ?></td>
                                <td><?= $booking['event_date'] ?></td>
                                <td><?= htmlspecialchars($booking['payment_method']) ?></td>
                                <td><?= $booking['status'] ?></td>
                                <td>
                                    <?php if($booking['status'] === 'Банкет завершен' && $booking['has_review'] == 0): ?>
                                        <a href="add_review.php?booking_id=<?= $booking['id'] ?>" class="action-link">✍️ Оставить отзыв</a>
                                    <?php elseif($booking['has_review'] > 0): ?>
                                        <span style="color:green;">✓ Отзыв оставлен</span>
                                    <?php else: ?>
                                        <span style="color:#999;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <div class="nav-links" style="margin-top: 20px; text-align: center;">
                <a href="create_booking.php" class="btn" style="display: inline-block; width: auto; padding: 10px 20px;">➕ Новая заявка</a>
                <a href="logout.php" class="btn btn-secondary" style="display: inline-block; width: auto; padding: 10px 20px; margin-left: 10px;">🚪 Выйти</a>
            </div>
            
        </div> <!-- /.content -->
    </div> <!-- /.container -->
    
    <script src="assets/js/slider.js"></script>
</body>
</html>
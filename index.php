<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/loginStyle.css">
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    <form action="index.php" method="post">
        <div class="form-group">
            <label for="login">Username</label>
            <input type="text" name="login" id="login" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <button type="submit">Login</button>
        </div>
        <a href="pages/forStudents.php">Я студент</a>
    </form>
</div>
<?php
require 'dbConnect.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['login'];
    $pass = $_POST['password'];

    // Поиск пользователя в базе данных
    $stmt = $pdo->prepare('SELECT * FROM teachers WHERE login = :login');
    $stmt->execute([
            ':login' => $user
    ]);
    $userRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pass == $userRecord['password']) {
        session_start(); // Стартуем сессию
        $_SESSION['username'] = $userRecord['login']; // Сохраняем данные в сессии
        header('Location: pages/faculties.php'); // Перенаправляем на другую страницу
        exit;
    } else {
        // Ошибка авторизации
        header('Location: index.php?error=Неверный логин или пароль');
        exit;
    }
}
?>
</body>
</html>

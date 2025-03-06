<?php
session_start();

// Проверяем, есть ли токен в сессии
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    header("Location: login.php");
    exit();
}

// Подключение к БД
include("./settings/connect_datebase.php");

// Расшифровка токена
$SECRET_KEY = 'cAtwa1kkEy';
$token = $_SESSION['token'];
$parts = explode('.', $token);

if (count($parts) === 3) {
    $header_base64 = $parts[0];
    $payload_base64 = $parts[1];
    $signatureJWT = $parts[2];

    // Проверяем подпись токена
    $unsignedToken = $header_base64 . '.' . $payload_base64;
    $signature = base64_encode(hash_hmac('sha256', $unsignedToken, $SECRET_KEY, true));

    if ($signatureJWT === $signature) {
        $payload_data = json_decode(base64_decode($payload_base64), true);
        $_SESSION['user_id'] = $payload_data['userId'];
        $role = $payload_data['role'];

        // Логируем заход
        file_put_contents('debug_log.txt', "Зашли в user.php. Роль пользователя: $role\n", FILE_APPEND);

        // Если роль не 0 (обычный пользователь), перенаправляем на admin.php
        if ($role == 1) {
            header("Location: admin.php");
            exit();
        }
    } else {
        unset($_SESSION['token']);
        header("Location: login.php");
        exit();
    }
} else {
    unset($_SESSION['token']);
    header("Location: login.php");
    exit();
}
?>


	<!DOCTYPE HTML>
<html>
	<head> 
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<meta charset="utf-8">
		<title> Личный кабинет </title>
		
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">
			<a href=# class = "singin"><img src = "img/ic-login.png"/></a>
		
			<a href=#><img src = "img/logo1.png"/></a>
			<div class="name">
				<a href="index.php">
					<div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
					Пермский авиационный техникум им. А. Д. Швецова
				</a>
			</div>
		</div>
		<div class="space"> </div>
		<div class="main">
			<div class="content">
				<input type="button" class="button" value="Выйти" onclick="logout()"/>
				<div class="name" style="padding-bottom: 0px;">Личный кабинет</div>
				
				
                <?php
                $user_id = $_SESSION['user_id'] ;
                if ($user_id > 0) {
					$stmt = $mysqli->prepare("SELECT id, login FROM users WHERE id = ?");

					if ($stmt) {
						// Привязываем параметр
						$stmt->bind_param('i', $user_id);
						
						// Выполняем запрос
						$stmt->execute();
						
						// Получаем результат
						$stmt->bind_result($id, $login);
						$stmt->fetch();
						
						// Закрываем запрос
						$stmt->close();
                } else {
                    echo "Ошибка авторизации!";
                }}
                ?>
				<div class="description">Добро пожаловать: <?php echo  $login; ?>
                <br>Ваш идентификатор: <?php echo $user_id ; ?>
			
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
    <script>
        function logout() {
            console.log("Запрос на выход отправлен...");
            $.ajax({
                url: 'ajax/logout.php', 
                type: 'POST',
                success: function () {
                    console.log("Выход выполнен, перезагрузка...");
                    window.location.href = "login.php";
                },
                error: function () {
                    console.log("Ошибка при выходе");
                }
            });
        }
    </script>
</body>
</html>

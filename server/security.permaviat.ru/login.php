<?php
	session_start();
	include("./settings/connect_datebase.php");

	$SECRET_KEY = 'cAtwa1kkEy'; 
	
	if (isset($_SERVER['HTTP_TOKEN'])) { 
		$_SESSION['token'] = $_SERVER['HTTP_TOKEN'];
	}
	
	if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
		$token = $_SESSION['token'];
		file_put_contents('log.txt', "$token\n", FILE_APPEND); 
		$parts = explode('.', $token);
	
		if (count($parts) === 3) {
			$header_base64 = $parts[0];
			$payload = $parts[1];
			$signatureJWT = $parts[2];
	
			$header = json_decode(base64_decode($header_base64), true);
			$unsignedToken = $header_base64 . '.' . $payload;
			$signature = base64_encode(hash_hmac('sha256', $unsignedToken, $SECRET_KEY, true));
	
			if ($signatureJWT === $signature) {
				$payload_data = json_decode(base64_decode($payload), true);
				$user_id = $payload_data['userId'];
				$role = $payload_data['role'];
	
				if ($role == 0) {
					header("Location: user.php");
				} else if ($role == 1) {
					header("Location: admin.php");
				}
				exit();
			} else {
				unset($_SESSION['token']);
			}
		}
	}
?>
<html>
	<head> 
		<meta charset="utf-8">
		<title> Авторизация </title>
		
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">
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
				<div class = "login">
					<div class="name">Авторизация</div>
				
					<div class = "sub-name">Логин:</div>
					<input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
					<div class = "sub-name">Пароль:</div>
					<input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
					
					<a href="regin.php">Регистрация</a>
					<br><a href="recovery.php">Забыли пароль?</a>
					<input type="button" class="button" value="Войти" onclick="LogIn()"/>
					<img src = "img/loading.gif" class="loading"/>
				</div>
				
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
		function LogIn() {
				var loading = document.getElementsByClassName("loading")[0];
				var button = document.getElementsByClassName("button")[0];
				
				var _login = document.getElementsByName("_login")[0].value;
				var _password = document.getElementsByName("_password")[0].value;
				loading.style.display = "block";
				button.className = "button_diactive";
				
				var data = new FormData();
				data.append("login", _login);
				data.append("password", _password);
				
				$.ajax({
					url: 'ajax/login_user.php',
					type: 'POST',
					data: data,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function (response) {
						if (response.token) {
							localStorage.setItem("token", response.token);
							location.href= "user.php"; 
						} else {
							alert(response.error || "Ошибка авторизации");
							loading.style.display = "none";
							button.className = "button";
						}
					},
					error: function() {
						console.log('Системная ошибка!');
						loading.style.display = "none";
						button.className = "button";
					}
				});
			}
			
			function PressToEnter(e) {
				if (e.keyCode == 13) {
					var _login = document.getElementsByName("_login")[0].value;
					var _password = document.getElementsByName("_password")[0].value;
					
					if(_password != "") {
						if(_login != "") {
							LogIn();
						}
					}
				}
			}
			
		</script>
	</body>
</html>
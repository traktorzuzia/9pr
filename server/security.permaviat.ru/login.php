<?php
	session_start();
	include("./settings/connect_datebase.php");
	
	$SECRET_KEY = 'cAtwa1kkEy'; 
	
	// Получаем заголовки
	$headers = getallheaders();
	if (isset($headers['token'])) {
		$_SESSION['token'] = $headers['token'];
	}
	
	if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
		$token = $_SESSION['token'];
		
		// Логируем токен только для отладки (удалите в продакшене)
		// file_put_contents('log.txt', "$token\n", FILE_APPEND); 
	
		$parts = explode('.', $token);
	
		if (count($parts) === 3) {
			$header_base64 = $parts[0];
			$payload = $parts[1];
			$signatureJWT = $parts[2];
	
			$header = json_decode(base64_decode($header_base64));
			
			// Убедимся, что алгоритм указан
			if (!isset($header->alg)) {
				unset($_SESSION['token']);
				header('HTTP/1.0 401 Unauthorized');
				exit('Неизвестный алгоритм');
			}
	
			$unsignedToken = $header_base64 . '.' . $payload;
			
			// Убедитесь, что используете правильный алгоритм
			$signature = base64_encode(hash_hmac('sha256', $unsignedToken, $SECRET_KEY, true));
	
			if ($signatureJWT === $signature) {
				$payload_data = json_decode(base64_decode($payload), true);
				if (isset($payload_data['userId']) && isset($payload_data['role'])) {
					$user_id = $payload_data['userId'];
					$role = $payload_data['role'];
	
					// Перенаправление на соответствующую страницу
					if ($role == 0) {
						header("Location: user.php");
					} else if ($role == 1) {
						header("Location: admin.php");
					}
					exit();
				}
			} else {
				unset($_SESSION['token']); // Если подпись неверна, удаляем токен
				header('HTTP/1.0 401 Unauthorized');
				exit('Неверная подпись токена');
			}
		} else {
			unset($_SESSION['token']); // Если токен некорректен, удаляем его
			header('HTTP/1.0 401 Unauthorized');
			exit('Некорректный токен');
		}
	} else {
		header('HTTP/1.0 401 Unauthorized');
		exit('Токен не предоставлен');
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
        
        var _login = document.getElementsByName("_login")[0].value.trim();
        var _password = document.getElementsByName("_password")[0].value.trim();
        
        // Проверка на пустые поля
        if (_login === "" || _password === "") {
            alert("Пожалуйста, заполните все поля.");
            return;
        }
        
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
                    location.href = "user.php"; 
                } else {
                    alert(response.error || "Ошибка авторизации");
                    loading.style.display = "none";
                    button.className = "button";
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Системная ошибка:', textStatus, errorThrown);
                alert('Произошла ошибка при связи с сервером. Попробуйте еще раз.');
                loading.style.display = "none";
                button.className = "button";
            }
        });
    }
    
    function PressToEnter(e) {
        if (e.keyCode === 13) { // Используйте строгое равенство
            LogIn(); // Вызываем функцию LogIn, если нажата клавиша Enter
        }
    }

    // Добавляем обработчик события для клавиатуры
    document.addEventListener('keydown', PressToEnter);
</script>
	</body>
</html>
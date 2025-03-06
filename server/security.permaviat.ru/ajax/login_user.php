<?php
session_start();

$login = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($login) || empty($password)) {
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(['error' => 'Логин и пароль обязательны']);
    exit;
}

$url = 'http://localhost:81/9pr/server/auth.permaviat.ru/index.php';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
curl_setopt($ch, CURLOPT_HEADER, false); 

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    $response_data = json_decode($response, true);
    
    if (isset($response_data['token'])) {
        $token = $response_data['token'];
        $_SESSION['token'] = $token;
        header('Content-Type: application/json');
        echo json_encode(['token' => $token]);
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo json_encode(['error' => 'Токен не получен']);
    }
} else {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Неверный логин или пароль']);
}
?>

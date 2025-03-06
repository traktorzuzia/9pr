<?php
    include("settings/connect.php");
  
    if (!isset($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_USER'])) { header('HTTP/1.0 403 Forbidden'); exit; }
    if (!isset($_SERVER['PHP_AUTH_PW']) || empty($_SERVER['PHP_AUTH_PW'])) { header('HTTP/1.0 403 Forbidden'); exit; }

    $login = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    $query_user = $mysqli->query("SELECT * FROM `users` WHERE `login` = '$login' AND `password` = '$password'");
    if ($read_user = $query_user->fetch_assoc()) {
        $header = ["typ" => "JWT", "alg" => "sha256"];
        $payload = [
            "userId" => $read_user['id'], 
            "role" => $read_user['roll']  
        ];
        $SECRET_KEY = 'cAtwa1kkEy'; 

        $base64Header = base64_encode(json_encode($header)); 
        $base64Payload = base64_encode(json_encode($payload)); 
        
        $unsignedToken = $base64Header . "." .$base64Payload; 
        $signature = hash_hmac('sha256', $unsignedToken, $SECRET_KEY, true);

        $base64Signature = base64_encode($signature); 
       
        $token =  $base64Header . '.'. $base64Payload . '.' . $base64Signature;

       http_response_code(200); 
        echo json_encode(['token' => $token]);
        } else {
        http_response_code(401); 
        echo json_encode(['error' => 'Invalid credentials']); 
            }
?>
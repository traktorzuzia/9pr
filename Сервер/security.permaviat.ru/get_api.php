<?php
$SECRET_KEY = "cAtwa1kkEy";
if(!isset(apache_request_headers()['token']) || empty(apache_request_headers()['token'])) { header('HTTP/1.0 403 Forbidden');exit;}

$token = apache_request_headers()['token'];
$header_base64 = explode(".", $token)[0];
$header = json_decode(base64_decode($header_base64));
$payload = explode('.', $token)[1];

$unsignedToken = $header_base64. '.' .$payload;
$signatureJWT = explode('.', $token)[2];
$signature = base64_encode(hash_hmac($header->alg, $unsignedToken, $SECRET_KEY));
if($signatureJWT == $signature) {
    header ("HTTP/1.0 200 OK");
   echo "Доступ к API разрешён."; 
}
else header("HTTP/1.0 401 Unauthorized");
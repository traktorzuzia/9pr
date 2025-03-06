<?php
session_start();
session_unset();
session_destroy();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
setcookie("token", "", time() - 3600, "/");

// Логируем выход
file_put_contents('debug_log.txt', "Пользователь вышел из системы.\n", FILE_APPEND);

echo "success";

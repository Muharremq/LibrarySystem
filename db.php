<?php

$server = "localhost";
$username = "root";
$password = "";
$dbname = "librarydb";

$connect = new mysqli($server, $username, $password, $dbname);

if(!$connect){
    echo "Database is not connected: {$connect -> connect_error}";
}

// requireLogin fonksiyonu
function requireLogin() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// İsteğe bağlı: Kullanıcının giriş yapıp yapmadığını kontrol eden fonksiyon
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
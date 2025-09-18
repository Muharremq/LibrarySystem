<?php
session_start();

// Tüm session verilerini temizle
session_unset();
session_destroy();

// Login sayfasına yönlendir
header("Location: login.php");
exit();
?>
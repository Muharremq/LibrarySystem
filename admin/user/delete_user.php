<?php
session_start();
include "../../db.php";

if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == "admin"){
        if(isset($_GET['user_id']) && !empty($_GET['user_id'])){
            $user_id = intval($_GET['user_id']); // Güvenlik için integer'a çevir
            
            // Önce kullanıcının var olup olmadığını kontrol et
            $check_sql = "SELECT id FROM users WHERE id = '$user_id' AND role = 'user'";
            $check_result = mysqli_query($conn, $check_sql);
            
            if(mysqli_num_rows($check_result) > 0){
                // Kullanıcı var, silebiliriz
                $sql = "DELETE FROM users WHERE id = '$user_id' AND role = 'user'";
                $result = mysqli_query($conn, $sql);

                if($result){
                    // Başarılı silme işlemi
                    $_SESSION['success_message'] = "Kullanıcı başarıyla silindi.";
                    header("Location: manage_users.php");
                    exit();
                } else {
                    // Veritabanı hatası
                    $_SESSION['error_message'] = "Veritabanı hatası: " . mysqli_error($conn);
                    header("Location: manage_users.php");
                    exit();
                }
            } else {
                // Kullanıcı bulunamadı
                $_SESSION['error_message'] = "Silinecek kullanıcı bulunamadı.";
                header("Location: manage_users.php");
                exit();
            }
        } else {
            // user_id parametresi eksik
            $_SESSION['error_message'] = "Geçersiz kullanıcı ID'si.";
            header("Location: manage_users.php");
            exit();
        }
    } else {
        // Admin değil
        header("Location: ../../dashboard.php");
        exit();
    }
} else {
    // Giriş yapmamış
    header("Location: ../../login.php");
    exit();
}
?>
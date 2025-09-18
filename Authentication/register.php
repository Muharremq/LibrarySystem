<?php
include "../db.php";

$error_message = "";
$success_message = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $name = mysqli_real_escape_string($connect, $_POST['name']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $plain_password = $_POST['password'];
    $role = mysqli_real_escape_string($connect, $_POST['role']);
    
    // Basit validasyon
    if(empty($name) || empty($email) || empty($plain_password)){
        $error_message = "Lütfen tüm alanları doldurun!";
    } elseif(strlen($plain_password) < 6){
        $error_message = "Şifre en az 6 karakter olmalıdır!";
    } else {
        // Email'in daha önce kullanılıp kullanılmadığını kontrol et (Prepared Statement ile)
        $check_email_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($connect, $check_email_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if($check_result && $check_result->num_rows > 0){
            $error_message = "Bu email adresi zaten kullanılıyor!";
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);
            
            // Parolayı hash'le
            $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
            
            // Kullanıcıyı kaydet (Prepared Statement ile)
            $insert_sql = "INSERT INTO users(name, email, password, role) VALUES(?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($connect, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "ssss", $name, $email, $hashed_password, $role);
            
            if(mysqli_stmt_execute($insert_stmt)){
                $success_message = "Kayıt başarılı! Giriş yapabilirsiniz.";
                // Formu temizle
                $name = $email = $plain_password = "";
                // 2 saniye bekle ve yönlendir
                header("refresh:2;url=login.php");
            } else {
                $error_message = "Kayıt sırasında hata oluştu: " . mysqli_error($connect);
            }
            mysqli_stmt_close($insert_stmt);
        }
    }
}
?>

<?php require '../view/partial/header.php'?>
<link rel="stylesheet" href="style/register.css">

<body>
    <div class="register">
        <h2>Hesap Oluştur</h2>
        
        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <input type="text" name="name" placeholder="Ad Soyad" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
            <input type="email" name="email" placeholder="E-posta" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            <input type="password" name="password" placeholder="Şifre (en az 6 karakter)" required>
            <input type="text" name="role" value="user" hidden>
            <button type="submit">Kayıt Ol</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #3498db; text-decoration: none;">Zaten hesabınız var mı? Giriş yapın</a>
        </div>
    </div>
</body>
</html>
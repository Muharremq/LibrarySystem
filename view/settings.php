<?php
// settings.php - Düzeltilmiş versiyon

require "../db.php";
// require "../Authentication/functions.php"; // requireLogin() fonksiyonu için

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// requireLogin(); // Her sayfa için giriş kontrolü, functions.php içinde tanımlı olmalı

if(!isset($_SESSION['user_id'])){
    header("Location: ../Authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Mevcut kullanıcı bilgilerini getir
$stmt = $connect->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Form gönderildiğinde (bu kısım değişmedi)
// ... (PHP POST işlemleri buraya gelecek) ...

// Header'ı dahil et
require_once "../view/partial/header.php";
?>
    <title>Ayarlar - Kütüphane</title>
    <!-- Ayarlar sayfasına özel CSS bağlantısı, eğer genel dashboard.css'ten farklıysa -->
    <!-- <link rel="stylesheet" href="../assets/settings.css"> --> 
    <!-- Bu link zaten header.php içinde dahil ediliyor, eğer çakışma olursa burayı silebilirsiniz -->
</head>
<body>
    <?php 
    // Navbar'ı dahil et
    require_once "../view/partial/navbar.php";
    ?>
    <link rel="stylesheet" href="style/settings.css">

    <div class="container">
        <div class="settings-container">
            <h1>Hesap Ayarları</h1>
            
            <?php if ($success_message): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Profil Bilgileri Güncelleme -->
            <div class="settings-section">
                <h2>Profil Bilgileri</h2>
                <form method="POST" class="settings-form">
                    <input type="hidden" name="update_type" value="profile">
                    
                    <div class="form-group">
                        <label for="name">İsim:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-update">Profili Güncelle</button>
                </form>
            </div>
            
            <!-- Şifre Değiştirme -->
            <div class="settings-section">
                <h2>Şifre Değiştir</h2>
                <form method="POST" class="settings-form">
                    <input type="hidden" name="update_type" value="password">
                    
                    <div class="form-group">
                        <label for="current_password">Mevcut Şifre:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Yeni Şifre:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Yeni Şifre Tekrar:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn-update">Şifreyi Güncelle</button>
                </form>
            </div>
            
            <div class="back-link">
                <a href="dashboard.php">← Ana Sayfaya Dön</a>
            </div>
        </div>
    </div>
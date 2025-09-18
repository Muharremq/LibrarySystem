<?php 
include "../db.php";
session_start();

$error_message = "";
$success_message = "";

if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == "user"){
        // Kullanıcı için stokta olan kitapları listele
        $sql = "SELECT * FROM books WHERE quantity > 0 ORDER BY id DESC";
        $result = mysqli_query($connect, $sql);
        
        if(!$result){
            $error_message = "Veritabanı hatası: " . mysqli_error($connect);
        }
    } else {
        // Admin ise admin dashboard'a yönlendir
        header("Location: admin/dashboard.php");
        exit();
    }
} else {
    // Giriş yapmamışsa login sayfasına yönlendir
    header("Location: Authentication/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Dashboard - Kütüphane</title>
    <link rel="stylesheet" href="style/dashboard.css">
</head>
<body>
    <?php 
    // Navbar'ı dahil et
    require_once "../view/partial/navbar.php";
    ?>
    
    <div class="content-wrapper">
        <?php if(isset($error_message) && !empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif($result && $result->num_rows > 0): ?>
            <div class="books-grid">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="book-card">
                        <?php if(!empty($row['image'])): ?>
                            <?php 
                            // Resim yolu düzeltmesi - image klasörü aynı dizinde olmalı
                            $image_path = "../admin/image/" . $row['image']; 
                            ?>
                            <?php if(file_exists($image_path)): ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                     class="book-image">
                            <?php else: ?>
                                <div class="no-image">Resim<br>Bulunamadı</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-image">Resim<br>Yok</div>
                        <?php endif; ?>
                        
                        <div class="book-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="book-author"><?php echo htmlspecialchars($row['author']); ?></div>
                        <div class="book-isbn">ISBN: <?php echo htmlspecialchars($row['isbn']); ?></div>
                        
                        <div>
                            <?php 
                            $quantity = (int)$row['quantity'];
                            if($quantity <= 5): ?>
                                <span class="quantity-badge low-stock"><?php echo $quantity; ?> Adet</span>
                            <?php else: ?>
                                <span class="quantity-badge"><?php echo $quantity; ?> Adet</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="borrow.php?book_id=<?php echo $row['id']; ?>" class="borrow-btn">Ödünç Al</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-books">
                <h3>Şu anda ödünç alınabilir kitap bulunmamaktadır.</h3>
                <p>Lütfen daha sonra tekrar kontrol edin.</p>
            </div>
        <?php endif; ?>
    </div>

<?php require "../view/partial/footer.php";?>
</body>
</html>
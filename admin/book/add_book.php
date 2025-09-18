<?php
include "../../db.php";
session_start();

$error_message = "";
$success_message = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $quantity = $_POST['quantity'];
    
    // Dosya yükleme kontrolü
    $image = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $image = $_FILES['image']['name'];
        
        // Dosya uzantısı kontrolü
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        
        if(!in_array($file_extension, $allowed_extensions)){
            $error_message = "Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir!";
        }
    }
    
    // Hata yoksa veritabanına kaydet
    if(empty($error_message)){
        // SQL syntax hatası düzeltildi - son parantez eksikti
        $sql = "INSERT INTO books(title, author, isbn, image, quantity) VALUES ('$title', '$author', '$isbn', '$image', '$quantity')";
        
        $result = mysqli_query($connect, $sql);
        
        if(!$result){
            $error_message = "Veritabanı hatası: " . mysqli_error($connect);
        } else {
            // Dosya yükleme işlemi
            if(!empty($image)){
                $image_location = $_FILES['image']['tmp_name']; // tmp_name doğru yazım
                $upload_location = "../image/"; // Slash eksikti
                
                // Klasör yoksa oluştur
                if(!file_exists($upload_location)){
                    mkdir($upload_location, 0777, true);
                }
                
                $target_file = $upload_location . $image;
                
                if(move_uploaded_file($image_location, $target_file)){
                    $success_message = "Kitap başarıyla eklendi!";
                    header("Location: view_books.php");
                } else {
                    $error_message = "Dosya yüklenirken hata oluştu!";
                }
            } else {
                $success_message = "Kitap başarıyla eklendi! (Resim yüklenmedi)";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitap Ekle - Admin Panel</title>
    <link rel="stylesheet" href="../partial/style/sidebar.css"> 
    <link rel="stylesheet" href="style/add_book.css">
</head>
<body>
    <?php require "../partial/sidebar.php";?>
    <div class="admin_add_book">
        <h2>Yeni Kitap Ekle</h2>
        
        <!-- Hata/Başarı mesajları -->
        <?php if(!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <form action="add_book.php" method="post" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Kitap Bilgileri</h3>
                
                <div class="form-group">
                    <label for="title">Kitap Başlığı <span class="required">*</span></label>
                    <input type="text" id="title" name="title" placeholder="Kitap başlığını girin" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="author">Yazar <span class="required">*</span></label>
                    <input type="text" id="author" name="author" placeholder="Yazar adını girin" required value="<?php echo isset($author) ? htmlspecialchars($author) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" placeholder="ISBN numarasını girin" value="<?php echo isset($isbn) ? htmlspecialchars($isbn) : ''; ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h3>Stok ve Görsel</h3>
                
                <div class="form-group">
                    <label for="quantity">Stok Adedi <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" placeholder="Stok adedini girin" min="0" required value="<?php echo isset($quantity) ? htmlspecialchars($quantity) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Kitap Kapağı</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small style="color: #7f8c8d; font-size: 12px;">JPG, JPEG, PNG veya GIF formatında olmalıdır.</small>
                </div>
            </div>
            
            <div class="button-group">
                <a href="http://localhost/LibrarySystem/admin/book/view_books.php" class="btn-secondary">İptal</a>
                <button type="submit">Kitap Ekle</button>
            </div>
        </form>
    </div>
</body>
</html>
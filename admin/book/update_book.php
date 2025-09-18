<?php
error_reporting(E_ALL); // Tüm hataları göster
ini_set('display_errors', 1); // Hataları ekrana yazdır

include "../../db.php"; // Veritabanı bağlantısı
session_start();

$error_message = ""; // Hata mesajları için
$success_message = ""; // Başarı mesajları için
$book_data = array(); // Kitap bilgilerini tutacak dizi


// Kullanıcı oturumu ve yetki kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
    header("Location: ../dashboard.php");
    exit();
}

$book_id = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = (int)$_GET['id'];

    // Kitabı veritabanından çekme işlemi
    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $book_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $book_data = mysqli_fetch_assoc($result); // Kitap verileri buraya atanır
        } else {
            $error_message = "Geçersiz kitap ID veya kitap bulunamadı!";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Veritabanı sorgu hazırlığı hatası: " . mysqli_error($connect);
    }

} else {
    // ID parametresi eksik veya geçersiz
    $error_message = "Geçersiz kitap ID!";
}

// Form gönderildiğinde güncelleme işlemi
// Sadece $book_data doluysa (yani geçerli bir kitap bulunduysa) güncelleme işlemini yap
if (empty($error_message) && isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $quantity = (int)$_POST['quantity'];
    
    // Validasyon
    if(empty($title) || empty($author) || empty($isbn) || $quantity < 0) {
        $error_message = 'Lütfen tüm zorunlu alanları doldurun ve geçerli bir stok miktarı girin!';
    } else {
        $image_name_for_db = $book_data['image']; // Mevcut resim adını koru

        // Resim yükleme işlemi
        if(isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
            $upload_dir = '../image/'; // image klasörüne kaydedilmesi daha tutarlı
            
            // Upload dizini yoksa oluştur
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_tmp = $_FILES['book_image']['tmp_name'];
            $file_name = $_FILES['book_image']['name'];
            $file_size = $_FILES['book_image']['size'];
            
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if(in_array($file_extension, $allowed_extensions)) {
                if($file_size <= 5242880) { // 5MB
                    // Benzersiz dosya adı oluştur
                    $new_file_name = 'book_' . $book_id . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_file_name;
                    
                    if(move_uploaded_file($file_tmp, $upload_path)) {
                        // Eski resmi sil (eğer varsa ve yeni bir resim yüklendiyse)
                        if(!empty($book_data['image']) && file_exists($upload_dir . $book_data['image'])) {
                            unlink($upload_dir . $book_data['image']);
                        }
                        $image_name_for_db = $new_file_name; // Yeni resmin adını kaydet
                    } else {
                        $error_message = 'Resim yüklenirken hata oluştu!';
                    }
                } else {
                    $error_message = 'Resim boyutu 5MB\'dan büyük olamaz!';
                }
            } else {
                $error_message = 'Sadece JPG, JPEG, PNG, GIF formatları kabul edilir!';
            }
        }
        
        // Hata yoksa veritabanını güncelle
        if(empty($error_message)) {
            $sql = "UPDATE books SET title = ?, author = ?, isbn = ?, image = ?, quantity = ? WHERE id = ?";
            $stmt = mysqli_prepare($connect, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssssii", $title, $author, $isbn, $image_name_for_db, $quantity, $book_id);
                
                if(mysqli_stmt_execute($stmt)) {
                    $success_message = 'Kitap başarıyla güncellendi!';
                    
                    // Güncel bilgileri tekrar çek (formu yenilemek için)
                    $sql_re_fetch = "SELECT * FROM books WHERE id = ?";
                    $stmt_re_fetch = mysqli_prepare($connect, $sql_re_fetch);
                    if ($stmt_re_fetch) {
                        mysqli_stmt_bind_param($stmt_re_fetch, "i", $book_id);
                        mysqli_stmt_execute($stmt_re_fetch);
                        $result_re_fetch = mysqli_stmt_get_result($stmt_re_fetch);
                        if ($result_re_fetch && mysqli_num_rows($result_re_fetch) > 0) {
                            $book_data = mysqli_fetch_assoc($result_re_fetch);
                        }
                        mysqli_stmt_close($stmt_re_fetch);
                    }
                } else {
                    $error_message = 'Güncelleme sırasında hata oluştu: ' . mysqli_error($connect);
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "Veritabanı güncelleme sorgusu hazırlığı hatası: " . mysqli_error($connect);
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
    <title>Kitap Güncelle - Kütüphane Yönetimi</title>
    <link rel="stylesheet" href="../partial/style/sidebar.css"> 
    <link rel="stylesheet" href="style/update_book.css">

</head>
<body>
    <?php require "../partial/sidebar.php";?>
    <div class="container">
        <div class="header">
            <h1>📚 Kitap Güncelle</h1>
            <p>Kitap bilgilerini düzenleyin</p>
        </div>

        <div class="form-container">
            <!-- Hata ve başarı mesajları -->
            <?php if (!empty($error_message)): ?>
                <div class="error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- Sadece geçerli kitap varsa formu göster -->
            <?php if(!empty($book_data)): ?>
            <form action="update_books.php?book_id=<?= $book_id ?>" method="post" enctype="multipart/form-data">
                
                <div class="form-section">
                    <h3>📖 Kitap Bilgileri</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Kitap Başlığı <span class="required">*</span></label>
                            <input type="text" name="title" id="title" 
                                   value="<?= htmlspecialchars($book_data['title'] ?? '') ?>" 
                                   placeholder="Kitap başlığını girin" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="author">Yazar <span class="required">*</span></label>
                            <input type="text" name="author" id="author" 
                                   value="<?= htmlspecialchars($book_data['author'] ?? '') ?>" 
                                   placeholder="Yazar adını girin" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN <span class="required">*</span></label>
                            <input type="text" name="isbn" id="isbn" 
                                   value="<?= htmlspecialchars($book_data['isbn'] ?? '') ?>" 
                                   placeholder="ISBN numarasını girin" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Stok Miktarı <span class="required">*</span></label>
                            <input type="number" name="quantity" id="quantity" 
                                   value="<?= htmlspecialchars($book_data['quantity'] ?? '0') ?>" 
                                   min="0" placeholder="0" required>
                        </div>
                    </div>

                </div>

                <div class="form-section">
                    <h3>🖼️ Kitap Resmi</h3>
                    
                    <div class="form-group">
                        <label for="book_image">Yeni Resim Seçin</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="book_image" id="book_image" accept="image/*">
                            <label for="book_image" class="file-input-label">
                                📁 Resim Seçin (JPEG, PNG, GIF - Max 5MB)
                            </label>
                        </div>
                        
                        <?php if(!empty($book_data['image']) && file_exists('../image/' . $book_data['image'])): ?>
                            <div class="current-image">
                                <p><strong>Mevcut Resim:</strong></p>
                                <img src="../image/<?= htmlspecialchars($book_data['image']) ?>" alt="Kitap Resmi">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" name="submit" class="btn btn-primary">
                        ✅ Kitabı Güncelle
                    </button>
                    <a href="view_books.php" class="btn btn-secondary">
                        ❌ İptal Et
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dosya seçildiğinde label'ı güncelle
        document.getElementById('book_image').addEventListener('change', function(e) {
            const label = document.querySelector('.file-input-label');
            if (e.target.files.length > 0) {
                label.textContent = '📁 ' + e.target.files[0].name;
            } else {
                label.textContent = '📁 Resim Seçin (JPEG, PNG, GIF - Max 5MB)';
            }
        });

        // Form validasyonu
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const author = document.getElementById('author').value.trim();
            const isbn = document.getElementById('isbn').value.trim();
            
            if (!title || !author || !isbn) {
                e.preventDefault();
                alert('Lütfen tüm zorunlu alanları doldurun!');
            }
        });
    </script>
</body>

</html>
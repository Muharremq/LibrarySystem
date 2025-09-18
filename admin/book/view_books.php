<?php
session_start();

// Değişkenleri başlangıçta tanımla
$result = null;
$error_message = "";
$success_message = "";

// Oturum kontrolü
if(!isset($_SESSION['user_id'])){
    header("Location: ../../Authentication/login.php"); // veya giriş sayfanıza uygun yol
    exit();
}

// Admin kontrolü
if($_SESSION['role'] != "admin"){
    header("Location: ../../view/dashboard.php");
    exit();
}

// Admin ise veritabanı işlemlerini yap
include "../../db.php";
$sql = "SELECT * FROM books ORDER BY id DESC";
$result = mysqli_query($connect, $sql);

if(!$result){
    $error_message = "Veritabanı hatası: " . mysqli_error($connect);
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitap Listesi - Library</title>
    <!-- ÖNCE sidebar.css, SONRA books.css dosyasını çağırın -->
    <link rel="stylesheet" href="../partial/style/sidebar.css"> 
    <link rel="stylesheet" href="style/view_book.css">

</head>
<body>
    <?php require "../partial/sidebar.php";?>


    <main class="main-content" id="mainContent">
    <!-- Geri kalan HTML kodunuz aynı kalacak -->
    <div class="page-header">
        <h1>Kitap Listesi</h1>
        <p>Kütüphanedeki tüm kitapları görüntüleyin</p>
    </div>

    <!-- Hata mesajları -->
    <?php if(!empty($error_message)): ?>
        <div class="message error" style="max-width: 1200px; margin: 0 auto 20px auto; padding: 12px 16px; border-radius: 6px; background: #fadbd8; color: #e74c3c; border: 1px solid #e74c3c; text-align: center;"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- İstatistik kartları -->
    <?php if($result && $result->num_rows > 0): ?>
        <?php
        // İstatistikleri hesapla
        $total_books = $result->num_rows;
        $total_quantity = 0;
        $low_stock_count = 0;
        $out_of_stock_count = 0;
        
        mysqli_data_seek($result, 0);
        while($stat_row = mysqli_fetch_assoc($result)) {
            $total_quantity += (int)$stat_row['quantity'];
            if($stat_row['quantity'] == 0) {
                $out_of_stock_count++;
            } elseif($stat_row['quantity'] <= 5) {
                $low_stock_count++;
            }
        }
        mysqli_data_seek($result, 0);
        ?>
        
        <div class="stats-cards">
            <div class="stat-card total">
                <h3><?php echo $total_books; ?></h3>
                <p>Toplam Kitap Türü</p>
            </div>
            <div class="stat-card available">
                <h3><?php echo $total_quantity; ?></h3>
                <p>Toplam Stok</p>
            </div>
        </div>
    <?php endif; ?>

     <!-- "Yeni Kitap Ekle" butonu -->
        <div style="text-align: right; margin-bottom: 20px; max-width: 1200px; margin-left: auto; margin-right: auto;">
            <a href="add_book.php" class="btn" style="...">Yeni Kitap Ekle</a>
        </div>

    <div class="table-wrapper">
        <table class="view-books">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Başlık</th>
                    <th>Yazar</th>
                    <th class="isbn-column">ISBN</th>
                    <th>Kapak</th>
                    <th>Stok</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td class="isbn-column"><?php echo htmlspecialchars($row['isbn']); ?></td>
                            <td>
                                <?php if(!empty($row['image'])): ?>
                                    <?php $image_path = "../image/" . $row['image']; ?>
                                    <?php if(file_exists($image_path)): ?>
                                        <img src="<?php echo $image_path; ?>" 
                                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                             class="book-image">
                                    <?php else: ?>
                                        <div class="no-image">Resim<br>Bulunamadı</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="no-image">Resim<br>Yok</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $quantity = (int)$row['quantity'];
                                if($quantity == 0): ?>
                                    <span class="quantity-badge out-of-stock"><?php echo $quantity; ?></span>
                                <?php elseif($quantity <= 5): ?>
                                    <span class="quantity-badge low-stock"><?php echo $quantity; ?></span>
                                <?php else: ?>
                                    <span class="quantity-badge"><?php echo $quantity; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="update_book.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Düzenle</a>
                                    <a href="delete_book.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" 
                                       onclick="return confirm('<?php echo addslashes($row['title']); ?> kitabını silmek istediğinizden emin misiniz?')">Sil</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <h3>Henüz kitap eklenmemiş</h3>
                            <p>Kütüphaneye kitap eklemek için aşağıdaki butona tıklayın</p>
                            <a href="add_book.php" class="btn">İlk Kitabı Ekle</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
     </main>

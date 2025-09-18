<?php
include "../../db.php";
session_start();

// Kullanıcı girişi ve yetki kontrolü
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

// ID parametresi kontrolü
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: view_books.php?error=no_id");
    exit();
}

$delete_id = mysqli_real_escape_string($connect, $_GET['id']);

// Kitabın var olup olmadığını kontrol et
$check_sql = "SELECT * FROM books WHERE id = '$delete_id'";
$check_result = mysqli_query($connect, $check_sql);

if(!$check_result || mysqli_num_rows($check_result) == 0){
    header("Location: view_books.php?error=book_not_found");
    exit();
}

$book_data = mysqli_fetch_assoc($check_result);

// Resim dosyasını sil (varsa)
if(!empty($book_data['image'])){
    $image_path = "../image/" . $book_data['image'];
    if(file_exists($image_path)){
        unlink($image_path);
    }
}

// Kitabı veritabanından sil
$delete_sql = "DELETE FROM books WHERE id = '$delete_id'";
$delete_result = mysqli_query($connect, $delete_sql);

if($delete_result){
    // Başarılı silme işlemi
    header("Location: view_books.php?success=deleted&book=" . urlencode($book_data['title']));
} else {
    // Hata durumu
    header("Location: view_books.php?error=delete_failed");
}

exit();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitapları Görüntüle - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Verdiğiniz CSS stillerini buraya ekleyin */
        body {
            font-family: "Inter", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: #34495e;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
            margin-top: 90px; /* Navbar için boşluk */
        }

        .view-books {
            width: 100%;
            max-width: 1200px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid #ecf0f1;
            animation: slideUp 0.6s ease-out;
            border-collapse: collapse;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Success/Error Messages */
        .message {
            padding: 12px 16px;
            border-radius: 6px;
            margin: 20px auto;
            font-weight: 500;
            text-align: center;
            max-width: 1200px;
        }

        .success {
            background: #d5f4e6;
            color: #27ae60;
            border: 1px solid #27ae60;
        }

        .error {
            background: #fadbd8;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            color: #ffffff;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 1.1rem;
            color: #bdc3c7;
        }

        /* Table Header */
        .view-books thead {
            background: #2c3e50;
        }

        .view-books thead th {
            padding: 20px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #34495e;
        }

        /* Table Body */
        .view-books tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #ecf0f1;
        }

        .view-books tbody tr:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .view-books tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .view-books tbody tr:nth-child(even):hover {
            background: #e9ecef;
        }

        .view-books tbody td {
            padding: 16px;
            color: #2c3e50;
            font-weight: 500;
            vertical-align: middle;
            border-bottom: 1px solid #ecf0f1;
        }

        /* Image cell styling */
        .book-image {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #ecf0f1;
            transition: transform 0.3s ease;
        }

        .book-image:hover {
            transform: scale(1.1);
            border-color: #3498db;
        }

        /* No image placeholder */
        .no-image {
            width: 60px;
            height: 80px;
            background: #bdc3c7;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7f8c8d;
            font-size: 12px;
            text-align: center;
            border: 2px solid #ecf0f1;
        }

        /* Actions column */
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
        }

        .btn-edit:hover {
            background: #e67e22;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .btn-view {
            background: #3498db;
            color: white;
        }

        .btn-view:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #95a5a6;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .empty-state .btn {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
        }
    </style>
    <?php require "../view/partial/admin_navbar.php";?>
</head>
<body>
    <div class="page-header">
        <h1>Kitap Yönetimi</h1>
        <p>Kütüphanedeki tüm kitapları görüntüleyin ve yönetin</p>
    </div>

    <?php if(!empty($success_message)): ?>
        <div class="message success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error_message)): ?>
        <div class="message error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if($result && mysqli_num_rows($result) > 0): ?>
        <table class="view-books">
            <thead>
                <tr>
                    <th>Resim</th>
                    <th>Kitap Adı</th>
                    <th>Yazar</th>
                    <th>ISBN</th>
                    <th>Kategori</th>
                    <th>Miktar</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php while($book = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <?php if(!empty($book['image'])): ?>
                            <img src="../image/<?php echo htmlspecialchars($book['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                 class="book-image">
                        <?php else: ?>
                            <div class="no-image">Resim Yok</div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td class="isbn-column"><?php echo htmlspecialchars($book['isbn']); ?></td>
                    <td><?php echo htmlspecialchars($book['category']); ?></td>
                    <td>
                        <span class="quantity-badge <?php 
                            if($book['quantity'] == 0) echo 'out-of-stock';
                            elseif($book['quantity'] < 5) echo 'low-stock';
                        ?>">
                            <?php echo $book['quantity']; ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-edit">Düzenle</a>
                            <a href="view_books.php?delete_id=<?php echo $book['id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Bu kitabı silmek istediğinizden emin misiniz?')">Sil</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="view-books">
            <div class="empty-state">
                <h3>Henüz Kitap Bulunmuyor</h3>
                <p>Kütüphaneye kitap eklemek için aşağıdaki butona tıklayın</p>
                <a href="add_book.php" class="btn">Kitap Ekle</a>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Success mesajını 3 saniye sonra gizle
        setTimeout(function() {
            const successMessage = document.querySelector('.message.success');
            if(successMessage) {
                successMessage.style.opacity = '0';
                setTimeout(() => successMessage.remove(), 300);
            }
        }, 3000);
    </script>
</body>
</html>
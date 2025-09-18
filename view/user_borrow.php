<?php
session_start();
include "../db.php";

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = "";
$borrowed_books = [];

try {
    // Kullanıcının ödünç aldığı kitapları getir
    $sql = "SELECT 
                t.id as transaction_id,
                t.book_id,
                t.issue_date,
                t.return_date,
                t.status,
                b.title,
                b.author,
                b.isbn,
                b.image,
                DATEDIFF(CURDATE(), t.issue_date) as days_borrowed,
                CASE 
                    WHEN t.return_date IS NULL THEN 
                        CASE 
                            WHEN DATEDIFF(CURDATE(), t.issue_date) > 14 THEN 'Gecikmiş'
                            WHEN DATEDIFF(CURDATE(), t.issue_date) > 10 THEN 'Yakında Teslim'
                            ELSE 'Normal'
                        END
                    ELSE 'Teslim Edildi'
                END as book_status
            FROM transactions t 
            INNER JOIN books b ON t.book_id = b.id 
            WHERE t.user_id = ? 
            ORDER BY t.issue_date DESC";
    
    $stmt = mysqli_prepare($connect, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $borrowed_books[] = $row;
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Veritabanı sorgusu hazırlanırken hata oluştu.";
    }
    
} catch (Exception $e) {
    $error_message = "Bir hata oluştu: " . $e->getMessage();
}

// İstatistikler
$total_borrowed = count($borrowed_books);
$currently_borrowed = count(array_filter($borrowed_books, function($book) {
    return $book['status'] === 'borrowed';
}));
$overdue = count(array_filter($borrowed_books, function($book) {
    return $book['book_status'] === 'Gecikmiş';
}));
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödünç Aldığım Kitaplar - Kütüphane Sistemi</title>
    <link rel="stylesheet" href="style/user_borrow.css">
</head>
<body>

    <?php 
    // Navbar'ı dahil et
    require_once "../view/partial/navbar.php";
    ?>
    <div class="container">
        <div class="header">
            <h1>📚 Ödünç Aldığım Kitaplar</h1>
        </div>

        <div class="stats-container">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_borrowed; ?></div>
                <div class="stat-label">Toplam Ödünç Alınan</div>
            </div>
            <div class="stat-card current">
                <div class="stat-number"><?php echo $currently_borrowed; ?></div>
                <div class="stat-label">Şu An Üzerimde</div>
            </div>
            <div class="stat-card overdue">
                <div class="stat-number"><?php echo $overdue; ?></div>
                <div class="stat-label">Geciken Kitap</div>
            </div>
        </div>

        <div class="content">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <strong>Hata:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($borrowed_books)): ?>
                <div class="no-books">
                    <h3>📖 Henüz Kitap Ödünç Almamışsınız</h3>
                    <a href="dashboard.php" class="btn btn-primary">Kitapları Keşfet</a>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($borrowed_books as $book): ?>
                        <div class="book-card">
                            <div class="book-image <?php echo empty($book['image']) ? 'no-image' : ''; ?>">
                                <?php if (!empty($book['image']) && file_exists("../admin/image/" . $book['image'])): ?>
                                    <img src="../admin/image/<?php echo htmlspecialchars($book['image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php else: ?>
                                    📚
                                <?php endif; ?>
                            </div>
                            
                            <div class="book-info">
                                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="book-author">Yazar: <?php echo htmlspecialchars($book['author']); ?></p>
                                
                                <div class="book-details">
                                    <div class="book-detail">
                                        <span>Ödünç Alma Tarihi:</span>
                                        <strong><?php echo date('d.m.Y', strtotime($book['issue_date'])); ?></strong>
                                    </div>
                                    
                                    <?php if ($book['return_date']): ?>
                                        <div class="book-detail">
                                            <span>Teslim Tarihi:</span>
                                            <strong><?php echo date('d.m.Y', strtotime($book['return_date'])); ?></strong>
                                        </div>
                                    <?php else: ?>
                                        <div class="book-detail">
                                            <span>Geçen Gün Sayısı:</span>
                                            <strong><?php echo $book['days_borrowed']; ?> gün</strong>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($book['isbn'])): ?>
                                        <div class="book-detail">
                                            <span>ISBN:</span>
                                            <strong><?php echo htmlspecialchars($book['isbn']); ?></strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="book-detail">
                                    <span>Durum:</span>
                                    <span class="status-badge <?php 
                                        switch($book['book_status']) {
                                            case 'Gecikmiş': echo 'status-danger'; break;
                                            case 'Yakında Teslim': echo 'status-warning'; break;
                                            case 'Teslim Edildi': echo 'status-returned'; break;
                                            default: echo 'status-normal';
                                        }
                                    ?>">
                                        <?php echo $book['book_status']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="navigation">
            <a href="dashboard.php" class="btn btn-back">Ana Sayfaya Dön</a>
        </div>
    </div>
</body>
</html>
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card.total .stat-number { color: #3498db; }
        .stat-card.current .stat-number { color: #27ae60; }
        .stat-card.overdue .stat-number { color: #e74c3c; }

        .content {
            padding: 30px;
        }

        .error-message {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .no-books {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-books h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .no-books p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .book-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-image.no-image {
            font-size: 48px;
            color: #adb5bd;
        }

        .book-info {
            padding: 20px;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .book-author {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .book-details {
            display: grid;
            gap: 8px;
            margin-bottom: 15px;
        }

        .book-detail {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .book-detail strong {
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-normal { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-danger { background: #f8d7da; color: #721c24; }
        .status-returned { background: #d1ecf1; color: #0c5460; }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            flex: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .navigation {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .navigation .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .stats-container {
                padding: 20px;
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .content {
                padding: 20px;
            }
            
            .books-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <?php 
    // Navbar'ı dahil et
    require_once "../view/partial/navbar.php";
    ?>
    <div class="container">
        <div class="header">
            <h1>📚 Ödünç Aldığım Kitaplar</h1>
            <p>Merhaba <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı'); ?>! Burada ödünç aldığınız tüm kitapları görebilirsiniz.</p>
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
                    <p>Kütüphane koleksiyonumuzu keşfedin ve ilginizi çeken kitapları ödünç alın!</p>
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
                                
                                <?php if ($book['status'] === 'borrowed'): ?>
                                    <div class="action-buttons">
                                        <a href="book_details.php?id=<?php echo $book['book_id']; ?>" class="btn btn-primary">Detayları Gör</a>
                                    </div>
                                <?php endif; ?>
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
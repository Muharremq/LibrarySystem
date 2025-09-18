<?php
session_start();
include "../db.php";

// Input validation
if (!isset($_GET['book_id']) || !is_numeric($_GET['book_id'])) {
    header("Location: dashboard.php");
    exit();
}

$book_id = (int)$_GET['book_id'];
if ($book_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "user") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Transaction başlat
mysqli_begin_transaction($connect);

try {
    // Stok kontrolü
    $stock_sql = "SELECT quantity FROM books WHERE id = ? AND quantity > 0";
    $stock_stmt = mysqli_prepare($connect, $stock_sql);
    mysqli_stmt_bind_param($stock_stmt, "i", $book_id);
    mysqli_stmt_execute($stock_stmt);
    $stock_result = mysqli_stmt_get_result($stock_stmt);
    
    if (mysqli_num_rows($stock_result) === 0) {
        throw new Exception("Bu kitap stokta bulunmuyor.");
    }
    
    // Kullanıcı zaten bu kitabı almış mı kontrolü
    $user_sql = "SELECT COUNT(*) as count FROM transactions 
                 WHERE user_id = ? AND book_id = ? AND status = 'borrowed'";
    $user_stmt = mysqli_prepare($connect, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "ii", $user_id, $book_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $user_data = mysqli_fetch_assoc($user_result);
    
    if ($user_data['count'] > 0) {
        throw new Exception("Bu kitabı zaten ödünç almışsınız.");
    }
    
    // Transaction ekle
    $insert_sql = "INSERT INTO transactions (user_id, book_id, issue_date, status) 
                   VALUES (?, ?, CURDATE(), 'borrowed')";
    $insert_stmt = mysqli_prepare($connect, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $book_id);
    
    if (!mysqli_stmt_execute($insert_stmt)) {
        throw new Exception("Transaction eklenirken hata oluştu.");
    }
    
    // Stok güncelle
    $update_sql = "UPDATE books SET quantity = quantity - 1 WHERE id = ?";
    $update_stmt = mysqli_prepare($connect, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "i", $book_id);
    
    if (!mysqli_stmt_execute($update_stmt)) {
        throw new Exception("Stok güncellenirken hata oluştu.");
    }
    
    // Transaction commit
    mysqli_commit($connect);
    $success = true;
    $message = "Kitap başarıyla ödünç alındı!";
    
} catch (Exception $e) {
    mysqli_rollback($connect);
    $success = false;
    $message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitap Ödünç Alma</title>
    <link rel="stylesheet" href="style/borrow.css">
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="success-message">
                <h2>Başarılı!</h2>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php else: ?>
            <div class="error-message">
                <h2>Hata!</h2>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn">Ana Sayfaya Dön</a>
    </div>
</body>
</html>
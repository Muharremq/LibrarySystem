<?php
session_start();

// Oturum kontrolü
if(!isset($_SESSION['user_id'])){
    header("Location: ../Authentication/login.php");
    exit();
}

// Admin kontrolü
if($_SESSION['role'] != "admin"){
    header("Location: ../view/dashboard.php");
    exit();
}

// Veritabanı bağlantısı
include "../db.php";

// İstatistikleri çek
$stats = [
    'total_books' => 0,
    'total_members' => 0,
    'total_stock' => 0,
    'low_stock_books' => 0,
    'out_of_stock_books' => 0,
    'active_loans' => 0
];

// Toplam kitap türü sayısı
$sql_books = "SELECT COUNT(*) as count FROM books";
$result_books = mysqli_query($connect, $sql_books);
if($result_books) {
    $stats['total_books'] = mysqli_fetch_assoc($result_books)['count'];
}

// Toplam üye sayısı (admin hariç)
$sql_members = "SELECT COUNT(*) as count FROM users WHERE role != 'admin'";
$result_members = mysqli_query($connect, $sql_members);
if($result_members) {
    $stats['total_members'] = mysqli_fetch_assoc($result_members)['count'];
}

// Toplam stok sayısı ve stok durumu
$sql_stock = "SELECT 
    SUM(quantity) as total_stock,
    SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN quantity > 0 AND quantity <= 5 THEN 1 ELSE 0 END) as low_stock
    FROM books";
$result_stock = mysqli_query($connect, $sql_stock);
if($result_stock) {
    $stock_data = mysqli_fetch_assoc($result_stock);
    $stats['total_stock'] = $stock_data['total_stock'] ?? 0;
    $stats['out_of_stock_books'] = $stock_data['out_of_stock'] ?? 0;
    $stats['low_stock_books'] = $stock_data['low_stock'] ?? 0;
}

// Aktif ödünç verilen kitap sayısı (eğer böyle bir tablo varsa)
// Bu kısım ödünç alma sisteminize göre güncellenmelidir
$sql_loans = "SELECT COUNT(*) as count FROM loans WHERE return_date IS NULL";
$result_loans = mysqli_query($connect, $sql_loans);
if($result_loans) {
    $stats['active_loans'] = mysqli_fetch_assoc($result_loans)['count'];
} else {
    // Eğer loans tablosu yoksa 0 olarak ayarla
    $stats['active_loans'] = 0;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kütüphane Yönetim Sistemi - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_dashboard.css">
    <link rel="stylesheet" href="partial/sidebar.css">
</head>
<body>
<?php require "partial/sidebar.php"; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="user-info">
                <span>Hoş geldin, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</span>
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?></div>
            </div>
        </header>

        <!-- Dashboard Stats -->
        <section class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon books">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_books']); ?></h3>
                    <p>Toplam Kitap Türü</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon members">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_members']); ?></h3>
                    <p>Toplam Üye</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stock">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['total_stock']); ?></h3>
                    <p>Toplam Stok</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon loans">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['active_loans']); ?></h3>
                    <p>Aktif Ödünç</p>
                </div>
            </div>
        </section>

        <!-- Additional Stats -->
        <section class="additional-stats">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h4><i class="fas fa-exclamation-triangle text-warning"></i> Stok Durumu</h4>
                        <div class="info-item">
                            <span>Düşük Stok (≤5):</span>
                            <span class="badge badge-warning"><?php echo $stats['low_stock_books']; ?></span>
                        </div>
                        <div class="info-item">
                            <span>Tükenen Kitaplar:</span>
                            <span class="badge badge-danger"><?php echo $stats['out_of_stock_books']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card">
                        <h4><i class="fas fa-chart-line text-primary"></i> Özet Bilgiler</h4>
                        <div class="info-item">
                            <span>Ortalama Stok/Kitap:</span>
                            <span class="badge badge-info">
                                <?php 
                                echo $stats['total_books'] > 0 
                                    ? number_format($stats['total_stock'] / $stats['total_books'], 1) 
                                    : '0'; 
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span>Sistem Durumu:</span>
                            <span class="badge badge-success">Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function toggleMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            sidebar.classList.toggle('mobile-open');
            mainContent.classList.toggle('sidebar-open');
        }

        // Responsive sidebar için
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.querySelector('.sidebar').classList.remove('mobile-open');
                document.querySelector('.main-content').classList.remove('sidebar-open');
            }
        });
    </script>
</body>
</html>
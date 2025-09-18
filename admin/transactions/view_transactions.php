<?php
session_start();
include "../../db.php";

$result = null;
$error_message = "";

// Oturum ve yetki kontrolü
if(!isset($_SESSION['user_id'])){
    header("Location: ../../Authentication/login.php");
    exit();
}

if($_SESSION['role'] != "admin"){
    header("Location: ../../view/dashboard.php");
    exit();
}

// LEFT JOIN kullanarak tüm transaction kayıtlarını getir
// Kullanıcı silinmiş olsa bile transaction verisi görünecek
$sql = "SELECT ta.*, 
               COALESCE(u.name, CONCAT('Silinmiş Kullanıcı (ID: ', ta.user_id, ')')) as user_name,
               u.id as user_exists,
               b.title as book_title,
               b.author as book_author
        FROM transactions as ta 
        LEFT JOIN users as u ON ta.user_id = u.id 
        LEFT JOIN books as b ON ta.book_id = b.id
        ORDER BY ta.id DESC";

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
    <title>İşlem Geçmişi - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../partial/style/sidebar.css">
    <link rel="stylesheet" href="style/view_transactions.css">
</head>
<body>
     <?php require "../partial/sidebar.php";?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-content">
                    <h1 class="page-title">
                        <i class="fas fa-history"></i>
                        İşlem Geçmişi
                    </h1>
                    <p class="page-subtitle">Tüm kitap ödünç alma ve iade işlemlerini görüntüleyin</p>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-export" onclick="exportTransactions()">
                    <i class="fas fa-download"></i>
                    Dışa Aktar
                </button>
                <button class="btn btn-refresh" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i>
                    Yenile
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <?php if(!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Hata:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($result): ?>
            <?php 
            // İstatistikleri hesapla
            $total_transactions = mysqli_num_rows($result);
            $borrowed_count = 0;
            $returned_count = 0;
            $overdue_count = 0;
            $deleted_users_count = 0;
            
            if($total_transactions > 0) {
                mysqli_data_seek($result, 0);
                while($stat_row = mysqli_fetch_assoc($result)) {
                    if($stat_row['status'] == 'borrowed') $borrowed_count++;
                    if($stat_row['status'] == 'returned') $returned_count++;
                    if($stat_row['status'] == 'overdue') $overdue_count++;
                    if($stat_row['user_exists'] === null) $deleted_users_count++;
                }
                mysqli_data_seek($result, 0);
            }
            ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $total_transactions; ?></div>
                        <div class="stat-label">Toplam İşlem</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon borrowed">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $borrowed_count; ?></div>
                        <div class="stat-label">Ödünç Alınan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon returned">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $returned_count; ?></div>
                        <div class="stat-label">İade Edilen</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $overdue_count; ?></div>
                        <div class="stat-label">Geciken</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-group">
                <label for="statusFilter">Durum Filtresi:</label>
                <select id="statusFilter" onchange="filterTransactions()">
                    <option value="all">Tümü</option>
                    <option value="borrowed">Ödünç Alınan</option>
                    <option value="returned">İade Edilen</option>
                    <option value="overdue">Geciken</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchInput">Ara:</label>
                <input type="text" id="searchInput" placeholder="Kullanıcı adı veya kitap ara..." onkeyup="searchTransactions()">
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-table"></i> İşlem Listesi</h3>
                <div class="table-info">
                    Toplam <span id="recordCount"><?php echo $total_transactions ?? 0; ?></span> kayıt
                </div>
            </div>
            <div class="table-wrapper">
                <table class="transactions-table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>İşlem ID</th>
                            <th>Kullanıcı</th>
                            <th>Kitap Bilgisi</th>
                            <th>Ödünç Tarihi</th>
                            <th>İade Tarihi</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <?php 
                                $is_deleted_user = ($row['user_exists'] === null);
                                $status_class = 'status-' . strtolower($row['status']);
                                ?>
                                <tr data-status="<?= strtolower($row['status']); ?>">
                                    <td>
                                        <span class="transaction-id">#<?= htmlspecialchars($row['id']); ?></span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-name <?= $is_deleted_user ? 'deleted-user' : ''; ?>">
                                                <i class="<?= $is_deleted_user ? 'fas fa-user-times' : 'fas fa-user'; ?>"></i>
                                                <?= htmlspecialchars($row['user_name']); ?>
                                            </div>
                                            <div class="user-id">ID: <?= htmlspecialchars($row['user_id']); ?></div>
                                            <?php if($is_deleted_user): ?>
                                                <div class="user-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Kullanıcı silinmiş
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="book-info">
                                            <div class="book-title">
                                                <i class="fas fa-book"></i>
                                                <?= htmlspecialchars($row['book_title'] ?? 'Bilinmiyor'); ?>
                                            </div>
                                            <?php if($row['book_author']): ?>
                                                <div class="book-author">
                                                    <i class="fas fa-user-edit"></i>
                                                    <?= htmlspecialchars($row['book_author']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="book-id">ID: <?= htmlspecialchars($row['book_id']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <i class="fas fa-calendar-plus"></i>
                                            <?= htmlspecialchars(date('d.m.Y', strtotime($row['issue_date']))); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <?php if($row['return_date']): ?>
                                                <i class="fas fa-calendar-check"></i>
                                                <?= htmlspecialchars(date('d.m.Y', strtotime($row['return_date']))); ?>
                                            <?php else: ?>
                                                <span class="no-return">
                                                    <i class="fas fa-clock"></i>
                                                    Henüz iade edilmedi
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $status_class; ?>">
                                            <?php 
                                            switch($row['status']) {
                                                case 'borrowed': 
                                                    echo '<i class="fas fa-hand-holding"></i> Ödünç Alındı'; 
                                                    break;
                                                case 'returned': 
                                                    echo '<i class="fas fa-check-circle"></i> İade Edildi'; 
                                                    break;
                                                case 'overdue': 
                                                    echo '<i class="fas fa-exclamation-triangle"></i> Gecikmiş'; 
                                                    break;
                                                default: 
                                                    echo ucfirst($row['status']); 
                                                    break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="update_transactions.php?transaction_id=<?= $row['id']; ?>" 
                                               class="btn btn-edit"
                                               title="Düzenle">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-delete" 
                                                    onclick="deleteTransaction(<?= $row['id']; ?>, '<?= addslashes($row['user_name']); ?>')"
                                                    title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="no-data">
                                <td colspan="7">
                                    <div class="no-data-content">
                                        <i class="fas fa-inbox"></i>
                                        <h3>Henüz işlem kaydı yok</h3>
                                        <p>Kitap ödünç alma işlemleri burada görüntülenecek</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script>
        // Filter transactions by status
        function filterTransactions() {
            const filterValue = document.getElementById('statusFilter').value;
            const table = document.getElementById('transactionsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                if (row.classList.contains('no-data')) continue;
                
                const status = row.getAttribute('data-status');
                
                if (filterValue === 'all' || status === filterValue) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            updateRecordCount(visibleCount);
        }

        // Search transactions
        function searchTransactions() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('transactionsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                if (row.classList.contains('no-data')) continue;
                
                const text = row.textContent.toLowerCase();
                
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            updateRecordCount(visibleCount);
        }

        // Update record count
        function updateRecordCount(count) {
            document.getElementById('recordCount').textContent = count;
        }

        // Delete transaction with confirmation
        function deleteTransaction(transactionId, userName) {
            if (confirm(`Bu işlem kaydını silmek istediğinizden emin misiniz?\n\nİşlem ID: ${transactionId}\nKullanıcı: ${userName}`)) {
                window.location.href = `delete_transactions.php?transaction_id=${transactionId}`;
            }
        }

        // Export transactions (placeholder function)
        function exportTransactions() {
            alert('Dışa aktarma özelliği yakında eklenecek!');
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add any initialization code here
            console.log('Transactions page loaded successfully');
        });
    </script>
</body>
</html>
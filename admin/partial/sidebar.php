<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kütüphane Yönetim Sistemi - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../partial/sidebar.css">
</head>


<body>
    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Modern Sidebar -->
    <nav class="modern-sidebar" id="modernSidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <div class="brand-container">
                <div class="brand-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="brand-content">
                    <h1 class="brand-title">LibraryOS</h1>
                    <span class="brand-subtitle">Yönetim Paneli</span>
                </div>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <div class="nav-section">
            <div class="nav-title">ANA MENÜ</div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="http://localhost/LibrarySystem/admin/admin_dashboard.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                        <div class="nav-indicator"></div>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="http://localhost/LibrarySystem/admin/book/view_books.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="nav-text">Kitap Yönetimi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="http://localhost/LibrarySystem/admin/user/manage_users.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Üye Yönetimi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="http://localhost/LibrarySystem/admin/transactions/view_transactions.php" class="nav-link">
                        <div class="nav-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <span class="nav-text">Ödünç İşlemleri</span>
                    </a>
                </li>
            </ul>
        </div>
            <!-- User Profile -->
            <div class="user-profile">
                <div class="user-actions">
                    <button class="action-btn logout" title="Çıkış Yap" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('modernSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        // --- 1. KENAR ÇUBUĞUNU AÇMA/KAPAMA MANTIĞI ---
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                // Sadece sidebar'a değil, body'e de sınıf ekleyip kaldıracağız
                document.body.classList.toggle('sidebar-collapsed');
                sidebar.classList.toggle('collapsed');
                
                // Kullanıcının tercihini tarayıcı hafızasına kaydet
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }

        // --- 2. SAYFA YÜKLENDİĞİNDE ESKİ TERCİHİ YÜKLEME ---
        // Sayfa yüklendiğinde, hafızadaki durumu kontrol et
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
            sidebar.classList.add('collapsed');
        }

        // --- DİĞER KODLARINIZ (AKTİF SAYFA, MOBİL MENÜ VB.) ---
        // (Bu kodlar olduğu gibi kalabilir)
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPagePath = window.location.pathname;

        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (currentPagePath === linkPath) {
                link.classList.add('active');
            }
        });

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
            });
        }
    });

    // Global fonksiyonlar
    function confirmLogout() {
        if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
            window.location.href = 'http://localhost/LibrarySystem/Authentication/logout.php';
        }
    }

    function toggleMobileMenu() {
        const sidebar = document.getElementById('modernSidebar');
        if (sidebar) {
            sidebar.classList.toggle('mobile-open');
        }
    }
</script>
</body>
</html>
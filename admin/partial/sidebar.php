<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kütüphane Yönetim Sistemi - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Google Fonts - İsteğe Bağlı ama Önerilir */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

        /* Değişkenler */
        :root {
          --sidebar-bg: #1e293b;
          --sidebar-bg-hover: #334155;
          --sidebar-bg-active: #4f46e5;
          --sidebar-text: #cbd5e1;
          --sidebar-text-active: #ffffff;
          --sidebar-title-text: #94a3b8;
          --sidebar-icon-color: #94a3b8;
          --sidebar-width: 280px;
          --sidebar-width-collapsed: 80px;
          --header-height: 70px;
          --transition-speed: 0.3s;
          --primary-accent: #4f46e5;
          --warn-accent: #f59e0b;
          --shadow-color: rgba(0, 0, 0, 0.2);
          --font-family: "Poppins", sans-serif;
          --primary-color: #4a69bd;
          --secondary-color: #f6f9fc;
          --text-color: #333;
          --white-color: #fff;
          --border-color: #e1e4e8;
        }

        /* Body'ye başlangıç stili - sidebar açık durumda olacak */
        body {
          font-family: var(--font-family);
          margin: 0;
          background-color: var(--secondary-color);
          color: var(--text-color);
          transition: all var(--transition-speed) ease;
        }

        /* Ana Kenar Çubuğu Stili */
        .modern-sidebar {
          position: fixed;
          top: 0;
          left: 0;
          width: var(--sidebar-width);
          height: 100vh;
          background-color: var(--sidebar-bg);
          color: var(--sidebar-text);
          display: flex;
          flex-direction: column;
          z-index: 1000;
          transition: width var(--transition-speed) ease;
          font-family: var(--font-family);
        }

        /* Sidebar Başlığı */
        .sidebar-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 20px;
          height: var(--header-height);
          flex-shrink: 0;
          border-bottom: 1px solid var(--sidebar-bg-hover);
        }

        .brand-container {
          display: flex;
          align-items: center;
          gap: 15px;
          overflow: hidden;
          white-space: nowrap;
        }

        .brand-icon {
          font-size: 24px;
          color: var(--primary-accent);
        }

        .brand-content {
          opacity: 1;
          transition: opacity 0.2s ease;
        }

        .brand-title {
          margin: 0;
          font-size: 20px;
          font-weight: 600;
          color: var(--sidebar-text-active);
        }

        .brand-subtitle {
          font-size: 12px;
          color: var(--sidebar-title-text);
        }

        /* Sidebar Açma/Kapama Butonu */
        .sidebar-toggle {
          background: var(--sidebar-bg-hover);
          color: var(--sidebar-text);
          border: none;
          width: 32px;
          height: 32px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: transform var(--transition-speed) ease;
        }

        .sidebar-toggle:hover {
          background: var(--primary-accent);
          color: var(--sidebar-text-active);
        }

        /* Navigasyon Bölümü */
        .nav-section {
          padding: 15px 0;
          overflow-y: auto;
          flex-grow: 1;
        }

        .nav-title {
          padding: 0 25px 10px;
          font-size: 11px;
          font-weight: 600;
          color: var(--sidebar-title-text);
          text-transform: uppercase;
          letter-spacing: 0.5px;
          opacity: 1;
          transition: opacity 0.2s ease;
        }

        .nav-menu {
          list-style: none;
          padding: 0;
          margin: 0;
        }

        .nav-item {
          padding: 0 15px;
        }

        .nav-link {
          display: flex;
          align-items: center;
          padding: 12px 10px;
          text-decoration: none;
          color: var(--sidebar-text);
          border-radius: 8px;
          margin-bottom: 5px;
          position: relative;
          overflow: hidden;
          white-space: nowrap;
        }

        .nav-link:hover {
          background-color: var(--sidebar-bg-hover);
        }

        .nav-link.active {
          background-color: var(--sidebar-bg-active);
          color: var(--sidebar-text-active);
        }

        .nav-link.active .nav-icon {
          color: var(--sidebar-text-active);
        }

        .nav-icon {
          font-size: 18px;
          min-width: 50px;
          text-align: center;
          color: var(--sidebar-icon-color);
          transition: color var(--transition-speed) ease;
        }

        .nav-text,
        .nav-badge {
          opacity: 1;
          transition: opacity 0.2s ease;
        }

        /* Kullanıcı Profili */
        .user-profile {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 15px;
          border-top: 1px solid var(--sidebar-bg-hover);
          margin-top: auto;
        }

        .user-actions {
          opacity: 1;
          transition: opacity 0.2s ease;
        }

        .action-btn.logout {
          background: none;
          border: none;
          color: var(--sidebar-icon-color);
          font-size: 20px;
          cursor: pointer;
          padding: 5px;
        }

        .action-btn.logout:hover {
          color: #e74c3c;
        }

        /* -------- DARALTILMIŞ (COLLAPSED) DURUM -------- */
        .modern-sidebar.collapsed {
          width: var(--sidebar-width-collapsed);
        }

        .modern-sidebar.collapsed .sidebar-toggle {
          transform: rotate(180deg);
        }

        .modern-sidebar.collapsed .brand-content,
        .modern-sidebar.collapsed .nav-text,
        .modern-sidebar.collapsed .nav-badge,
        .modern-sidebar.collapsed .nav-title,
        .modern-sidebar.collapsed .user-actions {
          opacity: 0;
          pointer-events: none;
        }

        .modern-sidebar.collapsed .user-profile {
          justify-content: center;
        }

        /* Sidebar Overlay for Mobile */
        .sidebar-overlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.5);
          z-index: 999;
          opacity: 0;
          visibility: hidden;
          transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        /* Ana İçerik Alanı - Sidebar'ın yanında konumlanır */
        .main-content {
          margin-left: var(--sidebar-width); /* Başlangıçta sidebar genişliği kadar margin */
          padding: 20px;
          transition: margin-left var(--transition-speed) ease;
          min-height: 100vh;
        }

        /* Sidebar daraltıldığında ana içerik alanının margin'ını küçült */
        body.sidebar-collapsed .main-content {
          margin-left: var(--sidebar-width-collapsed);
        }

        /* Başlık (Header) Stilleri */
        .header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 10px 20px;
          background-color: var(--white-color);
          border-bottom: 1px solid var(--border-color);
          box-shadow: 0 2px 4px var(--shadow-color);
          margin-bottom: 20px;
          border-radius: 8px;
        }

        .header-left {
          display: flex;
          align-items: center;
        }

        .page-title {
          margin: 0;
          font-size: 24px;
          color: var(--text-color);
        }

        .user-info {
          display: flex;
          align-items: center;
          color: var(--text-color);
        }

        .user-avatar {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background-color: var(--primary-color);
          color: var(--white-color);
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
          margin-left: 10px;
        }

        /* Mobil Menü Butonu */
        .mobile-menu-btn {
          display: none;
          background: none;
          border: none;
          font-size: 20px;
          cursor: pointer;
          margin-right: 15px;
          color: var(--text-color);
        }

        /* Hızlı İşlemler Bölümü */
        .quick-actions {
          background-color: var(--white-color);
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 2px 4px var(--shadow-color);
        }

        .section-title {
          margin-top: 0;
          margin-bottom: 20px;
          font-size: 20px;
          color: var(--text-color);
        }

        /* Eylem Butonları için Grid Düzeni */
        .actions-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 20px;
        }

        .quick-action-btn {
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 20px;
          background-color: var(--primary-color);
          color: var(--white-color);
          text-decoration: none;
          border-radius: 8px;
          font-size: 16px;
          transition: background-color 0.3s;
        }

        .quick-action-btn:hover {
          background-color: #3b56a0;
        }

        .quick-action-btn i {
          margin-right: 10px;
        }

        /* Mobil Responsive */
        @media (max-width: 1024px) {
          .modern-sidebar {
            transform: translateX(-100%);
            transition: transform var(--transition-speed) ease;
          }

          .modern-sidebar.mobile-open {
            transform: translateX(0);
          }

          .modern-sidebar.mobile-open + .sidebar-overlay {
            opacity: 1;
            visibility: visible;
          }

          .main-content {
            margin-left: 0 !important;
          }

          .mobile-menu-btn {
            display: block;
          }
        }
    </style>
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
                            <i class="fas fa-books"></i>
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
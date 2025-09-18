<?php
// navbar.php
// Oturum zaten başlatılmış olmalı ve $_SESSION['name'] gibi bilgilere erişebilmeliyiz.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Misafir';
?>
<header>
    <div class="header-left">
        <span>Kütüphane Sistemi</span>
    </div>
    <div class="header-right">
        <span class="user-info">Hoşgeldin, <?php echo $user_name; ?>!</span>
        <a href="dashboard.php" class="header-btn">Ana Sayfa</a>
        <a href="user_borrow.php" class="header-btn">Ödünç Kitaplarım</a>
        <a href="settings.php" class="header-btn">Ayarlar</a>
        <a href="http://localhost/LibrarySystem/Authentication/login.php" class="header-btn logout-btn">Çıkış Yap</a>
    </div>
</header>

<style>

    /* navbar.css */

/* Header/Navbar Genel Stilleri */
header {
    background: linear-gradient(to right, #6a11cb, #2575fc); /* Mor-mavi gradient */
    color: white;
    padding: 15px 40px; /* Üst/alt 15px, sağ/sol 40px boşluk */
    display: flex;
    justify-content: space-between; /* Öğeleri iki yana yasla */
    align-items: center; /* Dikeyde ortala */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Hafif gölge */
    position: sticky; /* Sayfa kaydırıldığında yapışkan kalır */
    top: 0; /* Ekranın en üstüne yapışır */
    z-index: 1000; /* Diğer öğelerin üzerinde görünür */
}

/* Sol Taraftaki Logo/Başlık */
.header-left span {
    font-size: 1.8em; /* Büyük font boyutu */
    font-weight: 700; /* Kalın yazı tipi */
    letter-spacing: 0.5px; /* Harf aralığı */
    white-space: nowrap; /* Metnin tek satırda kalmasını sağlar */
}

/* Sağ Taraftaki Kullanıcı Bilgileri ve Butonlar */
.header-right {
    display: flex;
    align-items: center; /* Dikeyde ortala */
    gap: 20px; /* Öğeler arasında 20px boşluk */
}

.user-info {
    font-size: 1.1em;
    font-weight: 500;
    white-space: nowrap; /* Kullanıcı adının tek satırda kalmasını sağlar */
}

/* Tüm Header Butonları İçin Genel Stil */
.header-btn {
    background-color: rgba(255, 255, 255, 0.2); /* Yarı şeffaf beyaz arka plan */
    color: white;
    padding: 10px 18px;
    border-radius: 25px; /* Yuvarlak butonlar */
    text-decoration: none; /* Alt çizgiyi kaldır */
    font-size: 0.95em;
    font-weight: 600;
    transition: all 0.3s ease; /* Yumuşak geçişler */
    border: 1px solid rgba(255, 255, 255, 0.3); /* Hafif beyaz kenarlık */
    white-space: nowrap; /* Buton metinlerinin tek satırda kalmasını sağlar */
}

.header-btn:hover {
    background-color: rgba(255, 255, 255, 0.3); /* Hover'da daha belirgin arka plan */
    transform: translateY(-1px); /* Hafif yukarı kayma efekti */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Hafif gölge efekti */
}

/* Çıkış Yap Butonuna Özel Stil (isteğe bağlı, daha belirgin olması için) */
.header-btn.logout-btn {
    background-color: rgba(255, 99, 71, 0.6); /* Kırmızımsı ton */
    border-color: rgba(255, 99, 71, 0.8);
}

.header-btn.logout-btn:hover {
    background-color: rgba(255, 99, 71, 0.8);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Duyarlı Tasarım */
@media (max-width: 768px) {
    header {
        flex-direction: column; /* Küçük ekranlarda dikey düzen */
        gap: 15px; /* Öğeler arasında boşluk */
        padding: 15px 20px; /* Kenar boşluklarını azalt */
    }

    .header-right {
        flex-wrap: wrap; /* Butonların alt satıra geçmesini sağla */
        justify-content: center; /* Ortala */
        width: 100%; /* Tam genişlik kapla */
    }

    .user-info {
        margin-bottom: 5px; /* Kullanıcı bilgisi ile butonlar arasına boşluk */
    }
}

@media (max-width: 480px) {
    .header-left span {
        font-size: 1.5em; /* Daha küçük başlık */
    }

    .user-info {
        font-size: 1em; /* Daha küçük kullanıcı bilgisi */
    }

    .header-btn {
        padding: 8px 15px; /* Daha küçük buton dolgusu */
        font-size: 0.9em; /* Daha küçük buton metni */
        border-radius: 20px; /* Daha küçük yuvarlaklık */
    }
}
</style>
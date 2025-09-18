<?php
session_start();

$error_message = "";
$success_message = "";

if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == "admin"){
        include "../../db.php";
        $sql = "SELECT id, name, email, role FROM users WHERE role = 'user'";
        $result = mysqli_query($connect, $sql);

        if(!$result){
            $error_message = "Veritabanı hatası: " . mysqli_error($connect);
        }
    } else {
        header("Location: http://localhost/LibrarySystem/admin/admin_dashboard.php");
        exit();
    }
} else {
    header("Location: http://localhost/LibrarySystem/Authentication/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Yönetimi - Library</title>
    <link rel="stylesheet" href="../partial/style/sidebar.css"> 
    <link rel="stylesheet" href="user.css">
</head>
<body>
    <?php require "../partial/sidebar.php";?>

    <!-- ANA İÇERİĞİ BU KAPSAYICI İÇİNE ALIN -->
    <main class="main-content">
        
        <div class="page-header">
            <h1>Kullanıcı Yönetimi</h1>
            <p>Sistemdeki tüm standart kullanıcıları görüntüleyin</p>
        </div>

        <!-- Hata mesajları -->
        <?php if(!empty($error_message)): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="view-users-table"> <!-- Sınıf adını daha spesifik hale getirdim -->
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>İsim</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($row['id']); ?></td>
                                <td data-label="İsim"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td data-label="Rol"><span class="role-badge"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                <td data-label="İşlem">
                                    <a href="delete_user.php?user_id=<?php echo $row['id']; ?>" 
                                       onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')" 
                                       class="btn-delete">
                                       <i class="fas fa-trash-alt"></i> Sil
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <h3>Henüz kullanıcı yok</h3>
                                <p>Sistem kullanıcıları burada görünecek</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main> <!-- main-content bitti -->

    <!-- Font Awesome ikonları için script (eğer sidebar'da yoksa) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
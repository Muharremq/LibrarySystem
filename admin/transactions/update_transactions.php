<?php
session_start();
include "../../db.php";


if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == "admin"){
        if(isset($_GET['transaction_id'])){
            $transaction_id = $_GET['transaction_id'];

                    if(isset($_POST['submit'])){
            $return_date = $_POST['return_date'];
            $status = $_POST['status'];

            $sql = "update transactions set return_date = '$return_date', status='$status' where id = '$transaction_id'";
        $result = mysqli_query($connect, $sql);

        if(!$result){
            $error_message = "Veritabanı hatası: " . mysqli_error($connect);
        }else {
            header("Location: view_transaction.php");
        }
        }
        }else{
            header("Location: view_transaction.php");
        }
}else {
    header("Location: ../../view/dashboard.php");
    }
}else{
    header("Location: ../../Authentication/login.php");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşlemi Güncelle - Kütüphane Yönetimi</title>
    <link rel="stylesheet" href="../partial/style/sidebar.css">
    <link rel="stylesheet" href="style/update_transactions.css">
</head>
<body>
    <?php require "../partial/sidebar.php";?>

    <!-- ANA İÇERİĞİ BU KAPSAYICI İÇİNE ALIN -->
    <main class="main-content">
        <div class="form-container">
            <!-- Sayfa Başlığı -->
            <div class="page-header">
                <h1>İşlemi Güncelle</h1>
                <p>İşlem durumunu ve iade tarihini düzenleyin</p>
            </div>

            <!-- Hata Mesajı Alanı -->
            <?php if(!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="update_transactions.php?transaction_id=<?= htmlspecialchars($transaction_id) ?>" method="post">
                
                <!-- Form Grubu: İade Tarihi -->
                <div class="form-group">
                    <label for="return_date">İade Tarihi</label>
                    <!-- type="date" kullanmak kullanıcı deneyimini artırır -->
                    <input type="date" name="return_date" id="return_date" required>
                    <small>Kitabın iade edildiği tarihi seçin.</small>
                </div>

                <!-- Form Grubu: Durum -->
                <div class="form-group">
                    <label for="status">Durum</label>
                    <select name="status" id="status">
                        <option value="returned">İade Edildi (returned)</option>
                        <option value="borrowed">Ödünç Alındı (borrowed)</option>
                        <option value="overdue">Gecikmiş (overdue)</option>
                    </select>
                    <small>İşlemin güncel durumunu seçin.</small>
                </div>

                <!-- Buton Grubu -->
                <div class="btn-group">
                    <button type="submit" name="submit" class="btn btn-primary">
                        Güncelle
                    </button>
                    <a href="view_transaction.php" class="btn btn-secondary">
                        İptal
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
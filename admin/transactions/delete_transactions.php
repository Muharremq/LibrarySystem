<?php
session_start();
include "../../db.php";


if(isset($_SESSION['user_id'])){
    if($_SESSION['role'] == "admin"){
        if(isset($_GET['transaction_id'])){
            $transaction_id = $_GET['transaction_id'];
        }


            $return_date = $_POST['return_date'];

            $sql = "delete from transactions where id = '$transaction_id'";
        $result = mysqli_query($connect, $sql);

        if(!$result){
            $error_message = "Veritabanı hatası: " . mysqli_error($connect);
        }else {
            header("Location: view_transactions.php");
        }     
}else {
    header("Location: ../../view/dashboard.php");
    }
}else{
    header("Location: ../../Authentication/login.php");
}
?>
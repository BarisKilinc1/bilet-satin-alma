<?php
session_start();
include("../login_dosyalari/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {

    $kullanici_id = $_SESSION['user_id'];

    
    $conn->beginTransaction();

    $kontrol_sorgu = 'SELECT role, company_id FROM "User" WHERE id = :id';
    $hazir_kontrol_sorgu = $conn->prepare($kontrol_sorgu);
    $hazir_kontrol_sorgu->execute([":id" => $kullanici_id]);
    $kullanici = $hazir_kontrol_sorgu->fetch(PDO::FETCH_ASSOC);

    
    if ($kullanici && $kullanici['role'] === 'company') {
        $firma_sorgu = 'DELETE FROM "Bus_Company" WHERE id = :company_id';
        $hazir_firma_sorgu = $conn->prepare($firma_sorgu);
        $hazir_firma_sorgu->execute([":company_id" => $kullanici['company_id']]);
    }

    
    $sorgu = 'DELETE FROM "User" WHERE id = :id';
    $hazir_sorgu = $conn->prepare($sorgu);
    $hazir_sorgu->execute([":id" => $kullanici_id]);

   
    $conn->commit();

    
    session_destroy();
    header("Location: ../index.php?message=account_deleted");
    exit;
}

if (!isset($_SESSION['user_name']) || !isset($_SESSION['user_email']) || !isset($_SESSION['user_role'])) {
    header("Location: ../login_dosyalari/logout.php");
    exit;
}
?>

<?php
session_start();
require_once("../login_dosyalari/config.php");


if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'company') {
    $_SESSION['error_message'] = 'Yetkisiz erişim!';
    header("Location: ../firma_yonetim/sefer_duzenleme.php?page=seferler");
    exit;
}


if (($_POST['action'] ?? '') !== 'delete' || empty($_POST['sefer_id'])) {
    $_SESSION['error_message'] = 'Geçersiz istek!';
    header("Location: ../firma_yonetim/sefer_duzenleme.php?page=seferler");
    exit;
}

$sefer_id = $_POST['sefer_id'];
$kullanici_id = $_SESSION['user_id'];


$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$kontrol = $conn->prepare('
        SELECT t.id
        FROM "Trips" t
        JOIN "User" u ON t.company_id = u.company_id
        WHERE t.id = :sefer_id AND u.id = :user_id
    ');
$kontrol->execute([':sefer_id' => $sefer_id, ':user_id' => $kullanici_id]);
if (!$kontrol->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION['error_message'] = 'Sefer bulunamadı veya silme yetkiniz yok.';
    header("Location: ../firma_yonetim/sefer_duzenleme.php?page=seferler");
    exit;
}


$sil = $conn->prepare('DELETE FROM "Trips" WHERE id = :sefer_id');
$sil->execute([':sefer_id' => $sefer_id]);

$_SESSION['success_message'] = 'Sefer başarıyla silindi.';



header("Location: ../firma_yonetim/sefer_duzenleme.php?page=seferler");
exit;

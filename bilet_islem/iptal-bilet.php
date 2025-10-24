<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

include("../login_dosyalari/config.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Lütfen giriş yapın.'); window.location.href='../index.php';</script>";
    exit;
}

$kullanici_id = $_SESSION['user_id'];
$bilet_id = $_POST['ticket_id'] ?? null;

if (!$bilet_id) {
    echo "<script>alert('Geçersiz istek!'); window.history.back();</script>";
    exit;
}

$sorgu = 'SELECT t.id, t.status, t.total_price, tr.departure_time
          FROM "Tickets" t
          JOIN "Trips" tr ON t.trip_id = tr.id
          WHERE t.id = :bilet_id AND t.user_id = :kullanici_id';
$hazir_sorgu = $conn->prepare($sorgu);
$hazir_sorgu->execute([':bilet_id' => $bilet_id, ':kullanici_id' => $kullanici_id]);
$bilet = $hazir_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$bilet) {
    die("<script>alert('Bilet bulunamadı!'); window.history.back();</script>");
}

if ($bilet['status'] === 'canceled') {
    die("<script>alert('Bu bilet zaten iptal edilmiş!'); window.history.back();</script>");
}

if ($bilet['status'] === 'expired') {
    die("<script>alert('Bu biletin süresi dolmuş!'); window.history.back();</script>");
}

$simdi = new DateTime("now");
$kalkis_zamani = new DateTime($bilet['departure_time']);
$fark = $kalkis_zamani->getTimestamp() - $simdi->getTimestamp();

if ($fark < 0) {
    $guncelle = 'UPDATE "Tickets" SET status = "expired" WHERE id = :id';
    $hazir_guncelle = $conn->prepare($guncelle);
    $hazir_guncelle->execute([':id' => $bilet_id]);
    die("<script>alert('Bu sefer zaten gerçekleşti, bilet süresi doldu!'); window.history.back();</script>");
}

if ($fark < 3600) {
    die("<script>alert('Seferin kalkışına 1 saatten az kaldığı için bilet iptal edilemez!'); window.history.back();</script>");
}



$guncelle = 'UPDATE "Tickets" SET status = "canceled" WHERE id = :id';
$hazir_guncelle = $conn->prepare($guncelle);
$hazir_guncelle->execute([':id' => $bilet_id]);


$iade_tutar = floatval($bilet['total_price']);
$bakiye_guncelle = $conn->prepare('UPDATE "User" SET balance = balance + :iade WHERE id = :id');
$bakiye_guncelle->execute([':iade' => $iade_tutar, ':id' => $kullanici_id]);
$_SESSION['balance'] += $iade_tutar;

echo "<script>alert('Bilet başarıyla iptal edildi ve ücret iade edildi.'); window.location.href='/bilet_islem/bilet.php';</script>";
exit;


?>
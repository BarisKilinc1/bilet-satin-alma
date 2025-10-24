<?php
session_start();
include("../login_dosyalari/config.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Lütfen giriş yapın.'); window.location.href='../index.php';</script>";
    exit;
}

$kullanici_id = $_SESSION['user_id'];
$sefer_id = $_POST['sefer_id'] ?? null;
$koltuk_no = $_POST['koltuk_no'] ?? null;
$toplam_fiyat = floatval($_POST['toplam_fiyat'] ?? 0);
$kupon_kodu = trim($_POST['kupon_kodu'] ?? '');

if (!$sefer_id || !$koltuk_no) {
    echo "<script>alert('Eksik bilgi gönderildi!'); window.history.back();</script>";
    exit;
}

function uuid()
{
    $veri = random_bytes(16);
    $veri[6] = chr((ord($veri[6]) & 0x0f) | 0x40);
    $veri[8] = chr((ord($veri[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($veri), 4));
}


$koltuk_kontrol = $conn->prepare('SELECT bs.id
                                  FROM "Booked_Seats" bs
                                  JOIN "Tickets" t ON bs.ticket_id = t.id
                                  WHERE t.trip_id = :trip_id
                                  AND bs.seat_number = :seat_number
                                  AND t.status = "active"');
$koltuk_kontrol->execute([':trip_id' => $sefer_id, ':seat_number' => $koltuk_no]);
if ($koltuk_kontrol->fetch()) {
    echo "<script>alert('Bu koltuk az önce başka biri tarafından alındı!'); window.history.back();</script>";
    exit;
}


$conn->beginTransaction();


$hazir_sorgu = $conn->prepare('SELECT balance FROM "User" WHERE id = :id');
$hazir_sorgu->execute([':id' => $kullanici_id]);
$kullanici = $hazir_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$kullanici) {
    $conn->rollBack();
    echo "<script>alert('Kullanıcı bulunamadı!'); window.history.back();</script>";
    exit;
}

$bakiye = floatval($kullanici['balance']);


$sefer_sorgu = $conn->prepare('SELECT tr.company_id, bc.name as company_name 
                               FROM "Trips" tr
                               JOIN "Bus_Company" bc ON bc.id = tr.company_id
                               WHERE tr.id = :trip_id');
$sefer_sorgu->execute([':trip_id' => $sefer_id]);
$sefer = $sefer_sorgu->fetch(PDO::FETCH_ASSOC);

if (!$sefer) {
    $conn->rollBack();
    echo "<script>alert('Sefer bulunamadı.'); window.history.back();</script>";
    exit;
}


$kupon_mesaj = '';
if (!empty($kupon_kodu)) {
    $sorgu = 'SELECT * FROM "Coupons" 
              WHERE code = :code 
              AND expire_date >= date("now") 
              AND usage_limit > 0';
    $hazir_sorgu = $conn->prepare($sorgu);
    $hazir_sorgu->execute([':code' => $kupon_kodu]);
    $kupon = $hazir_sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$kupon) {
        $conn->rollBack();
        echo "<script>alert('Geçersiz veya süresi dolmuş kupon kodu.'); window.history.back();</script>";
        exit;
    }

    
    if ($kupon['company_id'] != 159753) {
        $kullanildi_mi = $conn->prepare('SELECT id FROM "User_Coupons"
                                         WHERE user_id = :user_id AND coupon_id = :coupon_id');
        $kullanildi_mi->execute([':user_id' => $kullanici_id, ':coupon_id' => $kupon['id']]);
        if ($kullanildi_mi->fetch()) {
            $conn->rollBack();
            echo "<script>alert('Bu kuponu zaten kullandınız.'); window.history.back();</script>";
            exit;
        }
    }

   
    if ($kupon['company_id'] != 159753 && $kupon['company_id'] != $sefer['company_id']) {
        $firma_sorgu = $conn->prepare('SELECT name FROM "Bus_Company" WHERE id = :id');
        $firma_sorgu->execute([':id' => $kupon['company_id']]);
        $kupon_firma = $firma_sorgu->fetchColumn();
        $conn->rollBack();
        echo "<script>alert('Bu kupon yalnızca {$kupon_firma} firmasında geçerlidir.'); window.history.back();</script>";
        exit;
    }

    $indirim_orani = floatval($kupon['discount']);
    $indirim_miktari = ($toplam_fiyat * $indirim_orani) / 100;
    $toplam_fiyat -= $indirim_miktari;

    $guncelle_kupon = $conn->prepare('UPDATE "Coupons" SET usage_limit = usage_limit - 1 WHERE id = :id');
    $guncelle_kupon->execute([':id' => $kupon['id']]);

   
    if ($kupon['company_id'] != 159753) {
        $kullanici_kupon_id = uuid();
        $kupon_kayit = $conn->prepare('INSERT INTO "User_Coupons" (id, user_id, coupon_id)
                                       VALUES (:id, :user_id, :coupon_id)');
        $kupon_kayit->execute([
            ':id' => $kullanici_kupon_id,
            ':user_id' => $kullanici_id,
            ':coupon_id' => $kupon['id']
        ]);
    }

    $kupon_mesaj = "Kupon başarıyla uygulandı (%$indirim_orani indirim).";
}


if ($bakiye < $toplam_fiyat) {
    $conn->rollBack();
    echo "<script>alert('Yetersiz bakiye! Gerekli tutar: {$toplam_fiyat} TL, mevcut: {$bakiye} TL'); window.history.back();</script>";
    exit;
}


$koltuk_kontrol2 = $conn->prepare('SELECT bs.id
                                  FROM "Booked_Seats" bs
                                  JOIN "Tickets" t ON bs.ticket_id = t.id
                                  WHERE t.trip_id = :trip_id
                                  AND bs.seat_number = :seat_number
                                  AND t.status = "active"');
$koltuk_kontrol2->execute([':trip_id' => $sefer_id, ':seat_number' => $koltuk_no]);
if ($koltuk_kontrol2->fetch()) {
    $conn->rollBack();
    echo "<script>alert('Bu koltuk az önce başka biri tarafından alındı!'); window.history.back();</script>";
    exit;
}


$yeni_bakiye = $bakiye - $toplam_fiyat;
$bakiye_guncelle = $conn->prepare('UPDATE "User" SET balance = :b WHERE id = :id');
$bakiye_guncelle->execute([':b' => $yeni_bakiye, ':id' => $kullanici_id]);
$_SESSION['balance'] = $yeni_bakiye;


$bilet_id = uuid();
$bilet_ekle = $conn->prepare('INSERT INTO "Tickets" (id, trip_id, user_id, status, total_price)
                              VALUES (:id, :trip_id, :user_id, "active", :total_price)');
$bilet_ekle->execute([
    ':id' => $bilet_id,
    ':trip_id' => $sefer_id,
    ':user_id' => $kullanici_id,
    ':total_price' => $toplam_fiyat
]);


$koltuk_id = uuid();
$koltuk_ekle = $conn->prepare('INSERT INTO "Booked_Seats" (id, ticket_id, seat_number)
                               VALUES (:id, :ticket_id, :seat_number)');
$koltuk_ekle->execute([
    ':id' => $koltuk_id,
    ':ticket_id' => $bilet_id,
    ':seat_number' => $koltuk_no
]);

$conn->commit();

echo "<script>
    alert('Bilet başarıyla satın alındı! " . ($kupon_mesaj ?? '') . "\\nYeni bakiyeniz: {$yeni_bakiye} TL');
    window.location.href='../bilet_islem/bilet.php';
</script>";

$conn = null;
exit;
?>
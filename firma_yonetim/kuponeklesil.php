<?php
function uuid()
{
    $veri = random_bytes(16);
    $veri[6] = chr((ord($veri[6]) & 0x0f) | 0x40);
    $veri[8] = chr((ord($veri[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($veri), 4));
}

function kupon_kodu_olustur($uzunluk = 10)
{
    $karakterler = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($karakterler), 0, $uzunluk);
}

$kullanici_id = $_SESSION['user_id'];
$firma_sorgu = "SELECT company_id FROM \"User\" WHERE id = :user_id";
$hazir_firma_sorgu = $conn->prepare($firma_sorgu);
$hazir_firma_sorgu->execute([":user_id" => $kullanici_id]);
$kullanici_firmasi = $hazir_firma_sorgu->fetch(PDO::FETCH_ASSOC);

$firma_id = $kullanici_firmasi['company_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["kuponekle"])) {
    $kupon_id = uuid();
    $kupon_kodu = kupon_kodu_olustur();
    $indirim_orani = $_POST["indirimoran"];
    $kullanim_limiti = trim($_POST["kullanımlimit"]);
    $tarih = $_POST["tarih"];

    $sorgu = 'INSERT INTO "Coupons" (id, code, discount, company_id, usage_limit, expire_date)
              VALUES (:id, :code, :discount, :company_id, :usage_limit, :expire_date)';
    $hazir_sorgu = $conn->prepare($sorgu);
    $hazir_sorgu->execute([
        ":id" => $kupon_id,
        ":code" => $kupon_kodu,
        ":discount" => $indirim_orani,
        ":company_id" => $firma_id,
        ":usage_limit" => $kullanim_limiti,
        ":expire_date" => $tarih
    ]);

    $_SESSION['success_message'] = 'Kupon başarıyla eklendi.';
    header("Location: sefer_duzenleme.php?page=kuponlar");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["kuponsil"])) {
    $kupon_sil_id = $_POST["kuponsilid"];

    $conn->beginTransaction();

    $sorgu = 'DELETE FROM "Coupons" WHERE id = :id';
    $hazir_sorgu = $conn->prepare($sorgu);
    $hazir_sorgu->execute([":id" => $kupon_sil_id]);
    $conn->commit();

    $_SESSION['success_message'] = 'Kupon başarıyla silindi.';
    header("Location: sefer_duzenleme.php?page=kuponlar");
    exit;
}
?>

<?php
session_start();
include("../login_dosyalari/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mevcut_sifre = $_POST['şimdikişifre'];
    $yeni_sifre = $_POST['yenişifre'];
    $sifre_onay = $_POST['şifreyionayla'];
    $kullanici_id = $_SESSION['user_id'];

    if ($mevcut_sifre == '' || $yeni_sifre == '' || $sifre_onay == '') {
        echo "<script>alert('Bu alanlar boş bırakılamaz.'); history.back();</script>";
        exit;
    }

    if ($yeni_sifre !== $sifre_onay) {
        echo "<script>alert('Şifreler Eşleşmiyor.'); history.back();</script>";
        exit;
    }

    if (strlen($yeni_sifre) < 6) {
        echo "<script>alert('Yeni şifre en az 6 karakter olmalıdır.'); history.back();</script>";
        exit;
    }

    $sorgu = "SELECT password FROM \"User\" WHERE id = :id";
    $hazir_sorgu = $conn->prepare($sorgu);
    $hazir_sorgu->execute([":id" => $kullanici_id]);
    $kullanici = $hazir_sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici && password_verify($mevcut_sifre, $kullanici['password'])) {
        $yeni_hash_sifre = password_hash($yeni_sifre, PASSWORD_DEFAULT);

        $guncelle_sorgu = "UPDATE \"User\" SET password = :password WHERE id = :id";
        $hazir_guncelle_sorgu = $conn->prepare($guncelle_sorgu);
        $hazir_guncelle_sorgu->execute([
            ":password" => $yeni_hash_sifre,
            ":id" => $kullanici_id
        ]);

        echo "<script>alert('Şifreniz başarıyla değiştirildi.'); window.location.href = 'profile.php';</script>";
        exit;
    } else {
        echo "<script>alert('Mevcut şifre hatalı.'); history.back();</script>";
        exit;
    }
}

header("Location: /profil_islem/profile.php");
exit;
?>

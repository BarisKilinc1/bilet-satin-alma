<?php
session_start();
include("../login_dosyalari/config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'company') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $kalkis_sehri = $_POST['departure_city'];
    $varis_sehri = $_POST['destination_city'];
    $kalkis_zamani = $_POST['departure_time'];
    $varis_zamani = $_POST['arrival_time'];
    $fiyat = $_POST['price'];
    $kapasite = $_POST['capacity'];

    $kullanici_id = $_SESSION['user_id'];
    $firma_sorgu = "SELECT company_id FROM \"User\" WHERE id = :user_id";
    $hazir_firma_sorgu = $conn->prepare($firma_sorgu);
    $hazir_firma_sorgu->execute([":user_id" => $kullanici_id]);
    $kullanici_firmasi = $hazir_firma_sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici_firmasi && !empty($kullanici_firmasi['company_id'])) {
        $firma_id = $kullanici_firmasi['company_id'];

       
            $sefer_id = uniqid('trip_', true);

            $sorgu = "INSERT INTO \"Trips\" (id, company_id, destination_city, arrival_time, departure_time, departure_city, price, capacity) 
                      VALUES (:id, :company_id, :destination_city, :arrival_time, :departure_time, :departure_city, :price, :capacity)";

            $hazir_sorgu = $conn->prepare($sorgu);
            $hazir_sorgu->execute([
                ":id" => $sefer_id,
                ":company_id" => $firma_id,
                ":destination_city" => $varis_sehri,
                ":arrival_time" => $varis_zamani,
                ":departure_time" => $kalkis_zamani,
                ":departure_city" => $kalkis_sehri,
                ":price" => $fiyat,
                ":capacity" => $kapasite
            ]);

            $_SESSION['success_message'] = "Sefer başarıyla oluşturuldu!";
            header("Location: sefer_duzenleme.php?page=seferler");
            exit;


    } else {
        $_SESSION['error_message'] = "Firma bilgileriniz bulunamadı!";
        header("Location: sefer_duzenleme.php?page=yenisefer");
        exit;
    }
}
?>

<?php
session_start();
include("../login_dosyalari/config.php");


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'company') {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim!']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {

    $sefer_id = $_POST['sefer_id'] ?? null;
    $kullanici_id = $_SESSION['user_id'];

    if (!$sefer_id) {
        echo json_encode(['success' => false, 'message' => 'Geçersiz istek!']);
        exit;
    }

    
    $kontrol_sorgu = "SELECT t.id 
                      FROM \"Trips\" t 
                      JOIN \"User\" u ON t.company_id = u.company_id 
                      WHERE t.id = :sefer_id AND u.id = :user_id";

    $hazir_kontrol_sorgu = $conn->prepare($kontrol_sorgu);
    $hazir_kontrol_sorgu->execute([
        ":sefer_id" => $sefer_id,
        ":user_id" => $kullanici_id
    ]);

    $sefer = $hazir_kontrol_sorgu->fetch(PDO::FETCH_ASSOC);

    if (!$sefer) {
        echo json_encode(['success' => false, 'message' => 'Sefer bulunamadı veya bu seferi silme yetkiniz yok.']);
        exit;
    }

    
    $silme_sorgu = "DELETE FROM \"Trips\" WHERE id = :sefer_id";
    $hazir_silme_sorgu = $conn->prepare($silme_sorgu);
    $silme_sorgu_sonuc = $hazir_silme_sorgu->execute([":sefer_id" => $sefer_id]);

    if ($silme_sorgu_sonuc) {
        echo json_encode(['success' => true, 'message' => 'Sefer başarıyla silindi.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sefer silinirken bir hata oluştu.']);
    }

    exit;
}
?>

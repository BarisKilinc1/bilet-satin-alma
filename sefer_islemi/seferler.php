<?php
session_start();
include("../login_dosyalari/config.php");

$kalkis_yeri = $_GET['nereden'] ?? '';
$varis_yeri = $_GET['nereye'] ?? '';
$tarih = $_GET['tarih'] ?? '';

$sorgu = "SELECT t.*, bc.name as company_name, bc.logo_path 
          FROM \"Trips\" t 
          JOIN \"Bus_Company\" bc ON t.company_id = bc.id 
          WHERE t.departure_city = :nereden 
          AND t.destination_city = :nereye
          AND datetime(t.departure_time) > datetime('now', '+3 hour')";

if (!empty($tarih) && strtotime($tarih) < strtotime(date('Y-m-d'))) {
    echo "<script>alert('Geçmiş tarihli bir gün seçemezsiniz.'); window.location.href='../index.php';</script>";
    exit;
}

$parametreler = [
    ":nereden" => $kalkis_yeri,
    ":nereye" => $varis_yeri
];

if (!empty($tarih)) {
    $sorgu .= " AND DATE(t.departure_time) = :tarih";
    $parametreler[":tarih"] = $tarih;
}

$sorgu .= " ORDER BY t.departure_time ASC";

$hazir_sorgu = $conn->prepare($sorgu);
$hazir_sorgu->execute($parametreler);
$sefer_listesi = $hazir_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Seferler | VINN</title>
    <link rel="stylesheet" href="/sefer_islemi/seferler.css">
</head>

<body>
    <!-- Header Başlangıç -->
    <header class="baslik">
        <a href="../index.php" class="Vınn">
            <img src="../images/logo.png" alt="">
        </a>

        <nav class="navbar">
            <a href="../index.php">
                <i class="fa-solid fa-home"></i>
                Ana Sayfa</a>
            <span class="navbar">|</span>

            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="../index.php">
                    <i class="fa-solid fa-coins"></i>
                    Bakiye:
                    <?php echo htmlspecialchars($_SESSION['balance']); ?>
                </a>
                <span class="navbar">|</span>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa-solid fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        <i class="fa-solid fa-caret-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <a href="admin.php?page=dashboard">
                                <i class="fa-solid fa-grip"></i>
                                Dashboard
                            </a>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] == 'company'): ?>
                            <a href="Firmayönetim/seferdüzenleme.php">
                                <i class="fa-solid fa-building"></i>
                                Firma Yönetimi
                            </a>
                        <?php endif; ?>

                        <a href="../profil_islem/profile.php">
                            <i class="fa-solid fa-user"></i>
                            Profil
                        </a>
                        <a href="../bilet_islem/bilet.php">
                            <i class="fa-solid fa-ticket"></i>
                            Biletler
                        </a>
                        <a href="../login_dosyalari/logout.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Çıkış Yap
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="#" id="register">
                    <i class="fa-solid fa-user-plus"></i>
                    Kayıt Ol
                </a>
                <span class="navbar">|</span>
                <a href="#" id="login">
                    <i class="fa-solid fa-user"></i>
                    Giriş Yap
                </a>
            <?php endif; ?>
        </nav>
    </header>
    <!-- Header Bitiş -->
    <!-- Home Başlangıç -->
    <section class="home">
        <div class="seferler">
            <?php if (count($sefer_listesi) > 0): ?>
                <?php foreach ($sefer_listesi as $sefer): ?>
                    <?php
                    $kalkis = new DateTime($sefer['departure_time']);
                    $varis = new DateTime($sefer['arrival_time']);
                    ?>
                    <div class="sefer">
                        <div class="ust-bilgiler">
                            <div class="logo">
                                <img src="<?= htmlspecialchars('../admin_panel/' . ($sefer['logo_path'] ?? 'logolar/default.png')) ?>"
                                    alt="<?= htmlspecialchars($sefer['company_name']) ?>">
                            </div>


                            <div class="saat">
                                <label for="ikon">Kalkış Saati</label>
                                <div class="ikon">
                                    <i class="fa-regular fa-clock"></i>
                                    <span class="otobüssaati">
                                        <?= date('H:i', strtotime($sefer['departure_time'])) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="gidisyeri">
                                <p class="gidisyeni">
                                    <?= htmlspecialchars($sefer['departure_city']) ?> -
                                    <?= htmlspecialchars($sefer['destination_city']) ?>
                                </p>
                            </div>
                            <div class="fiyat">
                                <span class="fiyat"><?= htmlspecialchars($sefer['price']) ?> TL</span>
                            </div>
                            <div class="satınal">
                                <button class="koltuk-sec-btn" data-sefer="<?= htmlspecialchars($sefer['id']) ?>">Seç</button>
                            </div>
                        </div>

                        <div class="sefer-detay" data-id="<?= htmlspecialchars($sefer['id']) ?>">
                            <div class="detay-icerik">
                                <p>Koltuk Seçimi</p>
                            </div>
                            <div class="otobus">
                                <img src="../images/otobus.png" alt="">
                                <div class="koltuk">
                                    <h1>Koltuk Seçiniz</h1>
                                    <select class="koltukinyo" id="koltuk-select-<?= htmlspecialchars($sefer['id']) ?>">
                                        <option value="">Koltuk Seç</option>
                                        <?php
                                        $koltuk_sorgu = 'SELECT bs.seat_number 
                                                         FROM "Booked_Seats" bs
                                                         JOIN "Tickets" t ON bs.ticket_id = t.id
                                                         WHERE t.trip_id = :trip_id
                                                         AND t.status = "active"';
                                        $hazir_koltuk_sorgu = $conn->prepare($koltuk_sorgu);
                                        $hazir_koltuk_sorgu->execute([':trip_id' => $sefer['id']]);
                                        $dolu_koltuklar = $hazir_koltuk_sorgu->fetchAll(PDO::FETCH_COLUMN);

                                        for ($i = 1; $i <= 41; $i++):
                                            if (in_array($i, $dolu_koltuklar)): ?>
                                                <option value="<?= $i ?>" disabled style="color: gray;">
                                                    Koltuk <?= $i ?> (Dolu)
                                                </option>
                                            <?php else: ?>
                                                <option value="<?= $i ?>">
                                                    Koltuk <?= $i ?>
                                                </option>
                                            <?php endif;
                                        endfor;
                                        ?>
                                    </select>
                                    <button class="satinal-btn" disabled>Satın Al</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="sefer-bulunamadi">
                    <i class="fa-solid fa-bus-slash"></i>
                    <h3>Sefer Bulunamadı</h3>
                    <p>Aradığınız kriterlere uygun sefer bulunmamaktadır.</p>
                    <a href="../index.php" class="tekrar-ara">Tekrar Ara</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- Home Bitiş -->
    <!--Footer Başlangıç  -->
    <section class="footer">
        <div class="footercontent">
            <div class="footerlogo">
                <img src="../images/logo.png" alt="">
            </div>
            <div class="sharebtn">
                <h3>Bizi Takip Edin</h3>
                <a href="" class="fab fa-facebook"></a>
                <a href="" class="fab fa-twitter"></a>
                <a href="" class="fab fa-instagram"></a>
                <a href="" class="fab fa-linkedin"></a>
            </div>
            <div class="footer-links">
                <h3>Kurumsal</h3>
                <a href="#">Hakkımızda</a>
                <a href="#">İletişim</a>
                <a href="#">Kariyer</a>
            </div>
            <div class="footer-links">
                <h3>Yardım</h3>
                <a href="#">Sıkça Sorulan Sorular</a>
                <a href="#">Gizlilik Politikası</a>
                <a href="#">Kullanım Koşulları</a>
            </div>
        </div>
        <p class="altyazı">© 2025 VINN Tüm hakları saklıdır.</p>
    </section>
    <!-- Footer Bitiş -->
    <?php include("../login_dosyalari/login.php"); ?>
    <?php include("../login_dosyalari/register.php"); ?>
    <script>
        const giris = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    </script>
    <script src="../scripts/seferler.js"></script>
</body>

</html>
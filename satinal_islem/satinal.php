<?php
session_start();
include("../login_dosyalari/config.php"); 


$sefer_id = $_GET['sefer'] ?? null;
$koltuk_no = $_GET['koltuk'] ?? null;


if (!$sefer_id || !$koltuk_no) {
    die("Hatalı istek!");
}


$sorgu = 'SELECT t.*, bc.name AS company_name, bc.logo_path 
          FROM "Trips" t
          JOIN "Bus_Company" bc ON t.company_id = bc.id
          WHERE t.id = :id';
$hazir_sorgu = $conn->prepare($sorgu);
$hazir_sorgu->execute([":id" => $sefer_id]);
$sefer = $hazir_sorgu->fetch(PDO::FETCH_ASSOC);


if (!$sefer) {
    die("Sefer bulunamadı!");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Bilet Satın Al | VINN</title>
    <link rel="stylesheet" href="/satinal_islem/satinal.css">
</head>

<body>
    <!--Header Başlangıç  -->
    <header class="baslik">

        <a href="index.php" class="Vınn">
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
                            <a href="../admin.php?page=dashboard">
                                <i class="fa-solid fa-grip"></i>
                                Dashboard
                            </a>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] == 'company'): ?>
                            <a href="../firma_yonetim/seferdüzenleme.php">
                                <i class="fa-solid fa-building"></i>
                                Firma Yönetimi
                            </a>
                        <?php endif; ?>

                        <a href="../profile.php">
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

                <!-- Giriş yapmadıysan -->
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
    <!-- Header Sonu -->
    <!-- Home Başlangıç -->
    <section class="home">
        <div class="profil">
            <h2>Bilet Bilgileri</h2>
            <div class="bilgiler">
                <div class="bilgilersol">
                    <div class="Guzergah">
                        <h1>Güzergah:</h1>
                        <p><?= htmlspecialchars($sefer['departure_city']) ?> -
                            <?= htmlspecialchars($sefer['destination_city']) ?></p>
                    </div>

                    <div class="Tarih">
                        <h1>Tarih:</h1>
                        <p><?= date('d.m.Y', strtotime($sefer['departure_time'])) ?></p>
                    </div>
                    <div class="Saat">
                        <h1>Kalkış Saati:</h1>
                        <p><?= date('H:i', strtotime($sefer['departure_time'])) ?></p>
                    </div>
                    <div class="Koltuk">
                        <h1>Koltuk No:</h1>
                        <p><?= htmlspecialchars($koltuk_no) ?></p>
                    </div>
                    <div class="Fiyat">
                        <h1>Fiyat:</h1>
                        <p><?= htmlspecialchars($sefer['price']) ?> TL</p>
                    </div>
                </div>
                <div class="bilgilersag">
                    <i class="fa-solid fa-ticket" style="color: #000000;"></i>
                </div>
            </div>

            
            <form action="odeme-islem.php" method="POST">
                <input type="hidden" name="sefer_id" value="<?= htmlspecialchars($sefer_id) ?>">
                <input type="hidden" name="koltuk_no" value="<?= htmlspecialchars($koltuk_no) ?>">
                <input type="hidden" name="toplam_fiyat" value="<?= htmlspecialchars($sefer['price']) ?>">

                <div class="kupon-alani">
                    <input type="text" name="kupon_kodu" placeholder="Kupon Kodunuzu Giriniz">
                    <button type="button" class="kupon-uygula">Kuponu Uygula</button>
                </div>

                <button type="submit" class="satinal-btn">Satın Al</button>
            </form>
        </div>
    </section>
    <!--Home Sonu  -->
    <!-- Footer -->
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

    <script src="../scripts/seferler.js"></script>
</body>

</html>

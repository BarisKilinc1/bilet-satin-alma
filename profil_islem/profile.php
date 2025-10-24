<?php
session_start();
include("../login_dosyalari/config.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Lütfen giriş yapın.'); window.location.href='../index.php';</script>";
    exit;
}

$kullanici_id = $_SESSION['user_id'];

$sorgu = 'SELECT t.id AS ticket_id, t.status, t.total_price, t.created_at, 
               tr.departure_city, tr.destination_city, tr.departure_time, 
               bs.seat_number
        FROM "Tickets" t
        JOIN "Trips" tr ON t.trip_id = tr.id
        JOIN "Booked_Seats" bs ON bs.ticket_id = t.id
        WHERE t.user_id = :user_id
        ORDER BY t.created_at DESC';

$hazir_sorgu = $conn->prepare($sorgu);
$hazir_sorgu->execute([':user_id' => $kullanici_id]);
$bilet_listesi = $hazir_sorgu->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Profil | VINN</title>
    <link rel="stylesheet" href="/profil_islem/profil.css">
</head>

<body>
    <!--Header Başlangıç  -->
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
                            <a href="../admin_panel/admin.php?page=dashboard">
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

                        <a href="/profil_islem/profile.php">
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
    <!-- Profil Başlangıç -->
    <section class="home">
        <div class="profil">
            <h2>Kullanıcı Bilgileri</h2>
            <div class="bilgiler">
                <div class="ad">
                    <p>Adınız Soyadınız:</p>
                    <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                </div>
                <div class="email">
                    <p>Email Adresiniz:</p>
                    <p><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                </div>
                <div class="rol">
                    <p>Rolünüz:</p>
                    <p><?php echo htmlspecialchars($_SESSION['user_role']); ?></p>
                </div>

                <div class="ayar">
                    <button id="sifreDegistirBtn" class="yazi">
                        <p>Şifrenizi Değiştirmek için tıklayın</p>
                    </button>
                    <button id="hesapSilBtn" class="ikon">
                        <i class="fa-solid fa-trash" style="color: #ffffff;"></i>
                    </button>
                </div>

                <div id="sifreDegistirForm" class="sifreForm">
                    <h3>Şifre Değiştir</h3>
                    <form method="POST" action="sifre_degistir.php">
                        <div class="genelForm">
                            <label>Mevcut Şifre</label>
                            <input type="password" name="şimdikişifre" required>

                            <label>Yeni Şifre</label>
                            <input type="password" name="yenişifre" required minlength="6">

                            <label>Yeni Şifre Tekrar</label>
                            <input type="password" name="şifreyionayla" required>

                            <div class="butonlar">
                                <button type="submit" class="btn-kaydet">Şifreyi Değiştir</button>
                                <button type="button" id="iptalBtn" class="btn-iptal">İptal</button>
                            </div>
                        </div>
                    </form>
                </div>

                <form id="hesapSilForm" method="POST" action="/profil_islem/hesapsil.php" style="display: none;">
                    <input type="hidden" name="delete_account" value="1">
                </form>
            </div>
        </div>
    </section>
    <!-- Profil Sonu -->
    <!-- Footer Başlangıç -->
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
    <!-- Footer Sonu -->

    <script src="../scripts/profil.js"></script>
</body>
</html>

<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
?>



<datalist class="datalist" id="sehirler">
    <?php

    $sehirler = ["Adana", "Adıyaman", "Afyonkarahisar", "Ağrı", "Amasya", "Ankara", "Antalya", "Artvin", "Aydın", "Balıkesir", "Bilecik", "Bingöl", "Bitlis", "Bolu", "Burdur", "Bursa", "Çanakkale", "Çankırı", "Çorum", "Denizli", "Diyarbakır", "Edirne", "Elazığ", "Erzincan", "Erzurum", "Eskişehir", "Gaziantep", "Giresun", "Gümüşhane", "Hakkari", "Hatay", "Isparta", "Mersin", "İstanbul", "İzmir", "Kars", "Kastamonu", "Kayseri", "Kırklareli", "Kırşehir", "Kocaeli", "Konya", "Kütahya", "Malatya", "Manisa", "Kahramanmaraş", "Mardin", "Muğla", "Muş", "Nevşehir", "Niğde", "Ordu", "Rize", "Sakarya", "Samsun", "Siirt", "Sinop", "Sivas", "Tekirdağ", "Tokat", "Trabzon", "Tunceli", "Şanlıurfa", "Uşak", "Van", "Yozgat", "Zonguldak", "Aksaray", "Bayburt", "Karaman", "Kırıkkale", "Batman", "Şırnak", "Bartın", "Ardahan", "Iğdır", "Yalova", "Karabük", "Kilis", "Osmaniye", "Düzce"];

    for ($i = 0; $i < count($sehirler); $i++) {
        echo "<option value='" . $sehirler[$i] . "'>";
    }
    ?>
</datalist>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <title>Vınn | Ana Sayfa</title>
    <link rel="stylesheet" href="style.css">




</head>

<body>

    <!-- HEADER KISMI -->
    <header class="baslik">

        <a href="index.php" class="Vınn">
            <img src="images/logo.png" alt="">
        </a>

        <nav class="navbar">
            <a href="index.php">
                <i class="fa-solid fa-home"></i>
                Ana Sayfa</a>
            <span class="navbar">|</span>


            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="index.php">
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
                            <a href="admin_panel/admin.php?page=dashboard">
                                <i class="fa-solid fa-grip"></i>
                                Dashboard
                            </a>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] == 'company'): ?>
                            <a href="firma_yonetim/sefer_duzenleme.php">
                                <i class="fa-solid fa-building"></i>
                                Firma Yönetimi
                            </a>
                        <?php endif; ?>

                        <a href="profil_islem/profile.php">
                            <i class="fa-solid fa-user"></i>
                            Profil
                        </a>
                        <a href="bilet_islem/bilet.php">
                            <i class="fa-solid fa-ticket"></i>
                            Biletler
                        </a>
                        <a href="/login_dosyalari/logout.php">
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
    <!--HEADER SONU  -->
    <!--Home Kısmı Başlangıç -->
    <section class="home">
        <div class="content">
            <h3>Biletini Al</h3>
            <p>Seferleri gözden geçir, yolculuğunu Vınn'la planla.<br>Konfor, hız ve güven — hepsi bir tık uzağında.</p>
        </div>
        <div class="aratmaarkaplan">

            <form action="/sefer_islemi/seferler.php" method="GET" class="aratma-form">
                <div class="aratma">
                    <input list="sehirler" id="nereden" name="nereden" class="aratma" placeholder="Nereden" required>

                    <button type="button" id="degistir">
                        <i class="fa-solid fa-right-left"></i>
                    </button>

                    <input list="sehirler" id="nereye" name="nereye" class="aratma" placeholder="Nereye" required>
                    <input type="date" id="tarih" name="tarih" class="aratma" placeholder="Gidiş Tarihi" min="">


                    <button type="submit" class="button">Ara</button>
                </div>
            </form>
        </div>
    </section>
    <!-- Home Sonu -->
    <!-- Fırsatlar Başlangıç -->
    <section class="firsatlar">
        <h1>VINN'dan kaçırılmayacak fırsatlar</h1>
        <div class="kutucuklar">

            <div class="kutucuk">
                <div class="gorseller">
                    <a href="images/Kupon1.jpg" target="_blank">
                        <img src="images/Kupon1.jpg">
                    </a>

                </div>
            </div>
            <div class="kutucuk">
                <div class="gorseller">
                    <a href="images/Kupon2.jpg" target="_blank">
                        <img src="images/Kupon2.jpg">
                    </a>

                </div>
            </div>
            <div class="kutucuk">
                <div class="gorseller">
                    <a href="images/Kupon3.jpg" target="_blank">
                        <img src="images/Kupon3.jpg">
                    </a>

                </div>
            </div>
            <div class="kutucuk">
                <div class="gorseller">
                    <a href="images/Kupon4.jpg" target="_blank">
                        <img src="images/Kupon4.jpg">
                    </a>

                </div>
            </div>
        </div>
    </section>
    <!-- Fırsatlar Sonu -->
    <!-- Populerseferlerbaşlangıç -->
    <section class="populersefer">
        <h1>Popüler Otobüs Seferleri</h1>
        <div class="populerseferler">

            <div class="seferler">

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Ankara&nereye=İstanbul&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Ankara-İstanbul</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=İstanbul&nereye=İzmir&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">İstanbul-İzmir</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=İstanbul&nereye=Aydın&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">İstanbul-Aydın</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=İstanbul&nereye=Bursa&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">İstanbul-Bursa</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=İstanbul&nereye=Antalya&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">İstanbul-Antalya</h2>
                    </a>
                </div>

            </div>

            <div class="seferler">

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Antalya&nereye=Konya&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Antalya-Konya</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Antalya&nereye=Aydın&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Antalya-Aydın</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Antalya&nereye=Adana&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Antalya-Adana</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Denizli&nereye=Antalya&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Denizli-Antalya</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Ankara&nereye=Konya&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Adana-Konya</h2>
                    </a>
                </div>

            </div>

            <div class="seferler">

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=İstanbul&nereye=Ankara&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Adana-Ankara</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Adana&nereye=Denizli&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Adana-Denizli</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Çankırı&nereye=Ankara&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Çankırı-Ankara</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=Eskişehir&nereye=Konya&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Eskişehir-Konya</h2>
                    </a>
                </div>

                <div class="sefer">
                    <a href="/sefer_islemi/seferler.php?nereden=İstanbul&nereye=Ankara&tarih=<?php echo date('Y-m-d'); ?>">
                        <h2 class="gidilecekyer">Afyon-Muğla</h2>
                    </a>
                </div>

            </div>

        </div>
    </section>

    <!-- Populerseferler sonuç -->
    <!--Hakkımda Başlangıç  -->
    <section class="about">
        <h3 class="hakkımdabaslik">Hakkımızda </h3>
        <div class="row">
            <div class="image">
                <img src="images/hakkimizda.jpg" alt="">
            </div>
            <div class="content">
                <h3>Neden Bizi Tercih Etmelisiniz</h3>
                <p>VINN, yolculuğu sadece A’dan B’ye gitmekten ibaret görmez; her dakikanızı değerli kılmak için
                    çalışır.</p>
                <p>Güvenlik standartlarımız, deneyimli kaptanlarımız ve düzenli bakımdan geçen filomuzla iç huzuru
                    sunarız. Konfor detaylarıyla ise yolculuğu keyfe dönüştürürüz.</p>
                <p>Uygun fiyat, güçlü kampanyalar ve hızlı destek: Hepsi tek bir amaç için—sizi sevdiklerinize en iyi
                    şekilde ulaştırmak.</p>

            </div>
        </div>
    </section>
    <!-- Hakkımda Sonuç -->
    <!-- Footer Başlangıç -->
    <section class="footer">
        <div class="footercontent">

            <div class="footerlogo">
                <img src="images/logo.png" alt="">
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

    <?php include("login_dosyalari/login.php"); ?>
    <?php include("login_dosyalari/register.php"); ?>
    <script src="scripts/main.js"></script>
    <script src="scripts/login.js"></script>
</body>

</html>
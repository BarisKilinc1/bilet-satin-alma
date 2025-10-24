<?php
session_start();
include("../login_dosyalari/config.php");

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Lütfen giriş yapın.'); window.location.href='../index.php';</script>";
    exit;
}

$kullanici_id = $_SESSION['user_id'];

$conn->beginTransaction();


$sil_koltuklar = $conn->prepare('
    DELETE FROM "Booked_Seats"
    WHERE ticket_id IN (
        SELECT id FROM "Tickets" WHERE status IN ("expired", "canceled")
    )
');
$sil_koltuklar->execute();

$sil_biletler = $conn->prepare('DELETE FROM "Tickets" WHERE status IN ("expired", "canceled")');
$sil_biletler->execute();

$conn->commit();


$conn->exec('UPDATE "Tickets"
             SET status = "expired"
             WHERE status = "active"
             AND trip_id IN (
                 SELECT id FROM "Trips"
                 WHERE datetime(departure_time) < datetime("now", "+3 hour")
             )');


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
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Biletlerim | VINN</title>
    <link rel="stylesheet" href="/bilet_islem/biletler.css">
</head>

<body>
    <header class="baslik">
        <a href="../index.php" class="Vınn">
            <img src="../images/logo.png" alt="">
        </a>

        <nav class="navbar">
            <a href="../index.php">
                <i class="fa-solid fa-home"></i> Ana Sayfa
            </a>
            <span class="navbar">|</span>

            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="../index.php">
                    <i class="fa-solid fa-coins"></i>
                    Bakiye: <?= htmlspecialchars($_SESSION['balance']) ?>
                </a>
                <span class="navbar">|</span>

                <div class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa-solid fa-user"></i>
                        <?= htmlspecialchars($_SESSION['user_name']) ?>
                        <i class="fa-solid fa-caret-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <a href="../admin_panel/admin.php?page=dashboard">
                                <i class="fa-solid fa-grip"></i> Dashboard
                            </a>
                        <?php endif; ?>

                        <?php if ($_SESSION['user_role'] == 'company'): ?>
                            <a href="../firma_yonetim/sefer_duzenleme.php">
                                <i class="fa-solid fa-building"></i> Firma Yönetimi
                            </a>
                        <?php endif; ?>

                        <a href="../profil_islem/profile.php"><i class="fa-solid fa-user"></i> Profil</a>
                        <a href="../bilet_islem/bilet.php"><i class="fa-solid fa-ticket"></i> Biletler</a>
                        <a href="../login_dosyalari/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="#" id="register"><i class="fa-solid fa-user-plus"></i> Kayıt Ol</a>
                <span class="navbar">|</span>
                <a href="#" id="login"><i class="fa-solid fa-user"></i> Giriş Yap</a>
            <?php endif; ?>
        </nav>
    </header>

    <section class="home">
        <div class="biletler">
            <h2>Biletlerim</h2>

            <?php if (count($bilet_listesi) > 0): ?>
                <table class="bilet-tablosu">
                    <tr>
                        <th>Güzergah</th>
                        <th>Tarih</th>
                        <th>Koltuk</th>
                        <th>Fiyat</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    </tr>

                    <?php foreach ($bilet_listesi as $bilet): ?>
                        <?php
                        date_default_timezone_set('Europe/Istanbul');
                        $kalkis_zamani = new DateTime($bilet['departure_time']);
                        $simdi = new DateTime("now");
                        $fark_saniye = $kalkis_zamani->getTimestamp() - $simdi->getTimestamp();
                        $iptal_edilebilir = ($bilet['status'] === 'active' && $fark_saniye >= 3600);
                        $pdf_goster = ($bilet['status'] === 'active'); 
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($bilet['departure_city']) ?> -
                                <?= htmlspecialchars($bilet['destination_city']) ?>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($bilet['departure_time'])) ?></td>
                            <td><?= htmlspecialchars($bilet['seat_number']) ?></td>
                            <td><?= htmlspecialchars($bilet['total_price']) ?> TL</td>
                            <td class="durum <?= htmlspecialchars($bilet['status']) ?>">
                                <?= ucfirst($bilet['status']) ?>
                            </td>
                            <td>
                                <div class="bilet-islem-grup">
                                    <?php if ($iptal_edilebilir): ?>
                                        <form method="POST" action="iptal-bilet.php"
                                            onsubmit="return confirm('Bu bileti iptal etmek istiyor musunuz?');">
                                            <input type="hidden" name="ticket_id"
                                                value="<?= htmlspecialchars($bilet['ticket_id']) ?>">
                                            <button type="submit" class="iptal-btn">İptal Et</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="iptal-btn pasif" disabled>
                                            <?php if ($bilet['status'] === 'expired'): ?>
                                                Süresi Doldu
                                            <?php elseif ($bilet['status'] === 'canceled'): ?>
                                                İptal Edildi
                                            <?php else: ?>
                                                İptal Edilemez
                                            <?php endif; ?>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($pdf_goster): ?>
                                        <button class="pdf-btn"
                                            data-sefer="<?= htmlspecialchars($bilet['departure_city']) ?> - <?= htmlspecialchars($bilet['destination_city']) ?>"
                                            data-tarih="<?= date('d.m.Y H:i', strtotime($bilet['departure_time'])) ?>"
                                            data-koltuk="<?= htmlspecialchars($bilet['seat_number']) ?>"
                                            data-fiyat="<?= htmlspecialchars($bilet['total_price']) ?> TL">
                                            PDF
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p class="bilet-yok">Henüz hiç biletiniz yok.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="footer">
        <div class="footercontent">
            <div class="footerlogo"><img src="../images/logo.png" alt=""></div>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-font-roboto/dist/roboto.min.js"></script>
    <script src="../scripts/pdfolustur.js" defer></script>
    <script src="../scripts/profil.js" defer></script>
</body>

</html>
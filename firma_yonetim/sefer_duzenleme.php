<?php
session_start();
include("../login_dosyalari/config.php");
include("kuponeklesil.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'company') {
    header("Location: ../index.php");
    exit;
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
    <title>Firma Panel | VINN</title>
    <link rel="stylesheet" href="/firma_yonetim/sefer_duzenle.css">
</head>

<body>
    <header class="baslik">
        <a href="../index.php" class="Vınn">
            <img src="../images/logoblack.png" alt="">
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
                <a href="../profil_islem/profile.php">
                    <i class="fa-solid fa-user"></i>
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <section class="home">
        <div class="solbar">
            <div class="seferler">
                <a href="sefer_duzenleme.php?page=seferler"
                    class="<?php echo ($_GET['page'] ?? '') == 'seferler' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-bus"></i>
                    <p>Seferler</p>
                </a>
            </div>
            <div class="kuponlar">
                <a href="sefer_duzenleme.php?page=kuponlar"
                    class="<?php echo ($_GET['page'] ?? '') == 'kuponlar' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-ticket"></i>
                    <p>Kuponlar</p>
                </a>
            </div>
            <div class="yeni-sefer">
                <a href="sefer_duzenleme.php?page=yenisefer"
                    class="<?php echo ($_GET['page'] ?? '') == 'yenisefer' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-plus"></i>
                    <p>Sefer Ekle</p>
                </a>
            </div>

            <div class="cikis">
                <a href="/login_dosyalari/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <p>Çıkış</p>
                </a>
            </div>
        </div>

        <div class="icerik">
            <?php
            if (isset($_SESSION['success_message'])) {
                echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo "<script>alert('" . $_SESSION['error_message'] . "');</script>";
                unset($_SESSION['error_message']);
            }

            $sayfa = $_GET['page'] ?? 'seferler';

            if ($sayfa == 'seferler') {

                $kullanici_id = $_SESSION['user_id'];
                $firma_sorgu = "SELECT company_id FROM \"User\" WHERE id = :user_id";
                $hazir_firma_sorgu = $conn->prepare($firma_sorgu);
                $hazir_firma_sorgu->execute([":user_id" => $kullanici_id]);
                $kullanici_firmasi = $hazir_firma_sorgu->fetch(PDO::FETCH_ASSOC);

                if ($kullanici_firmasi && !empty($kullanici_firmasi['company_id'])) {
                    $firma_id = $kullanici_firmasi['company_id'];

                    $eski_sil = $conn->prepare('
                    DELETE FROM "Trips"
                    WHERE company_id = :company_id
                    AND datetime(departure_time) < datetime("now", "+3 hours")
                    ');
                    $eski_sil->execute([':company_id' => $firma_id]);

                    $sefer_sorgu = "SELECT t.*, bc.name as company_name 
                      FROM \"Trips\" t 
                      JOIN \"Bus_Company\" bc ON t.company_id = bc.id 
                      WHERE t.company_id = :company_id 
                      ORDER BY t.departure_time DESC";
                    $hazir_sefer_sorgu = $conn->prepare($sefer_sorgu);
                    $hazir_sefer_sorgu->execute([":company_id" => $firma_id]);
                    $seferler = $hazir_sefer_sorgu->fetchAll(PDO::FETCH_ASSOC);
                }
                ?>

                <div class="firmaseferler">
                    <h2>Firmanıza Ait Seferler</h2>

                    <?php if (count($seferler) > 0): ?>
                        <div class="seferlertablo">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Kalkış Yeri</th>
                                        <th>Varış Yeri</th>
                                        <th>Kalkış Zamanı</th>
                                        <th>Varış Zamanı</th>
                                        <th>Kapasite</th>
                                        <th>Fiyat</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($seferler as $sefer): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sefer['departure_city']) ?></td>
                                            <td><?= htmlspecialchars($sefer['destination_city']) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($sefer['departure_time'])) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($sefer['arrival_time'])) ?></td>
                                            <td><?= htmlspecialchars($sefer['capacity']) ?></td>
                                            <td><?= htmlspecialchars($sefer['price']) ?> TL</td>
                                            <td>
                                                <button class="btn-edit" onclick="editSefer('<?= $sefer['id'] ?>')">
                                                    <i class="fa-solid fa-pen-to-square" style="color: #000000;"></i>
                                                </button>
                                                <form method="POST" action="sefer_sil.php" style="display:inline;"
                                                    onsubmit="return confirm('Bu seferi silmek istediğinize emin misiniz?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="sefer_id"
                                                        value="<?= htmlspecialchars($sefer['id']) ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="fa-solid fa-trash" style="color:#ffffff;"></i>
                                                    </button>
                                                </form>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="no-sefer">Henüz hiç seferiniz bulunmamaktadır.</p>
                    <?php endif; ?>
                </div>

                
                <?php
            } elseif ($sayfa == 'kuponlar') {

                $kullanici_id = $_SESSION['user_id'];

                $firma_sorgu = 'SELECT company_id FROM "User" WHERE id = :user_id';
                $hazir_firma_sorgu = $conn->prepare($firma_sorgu);
                $hazir_firma_sorgu->execute([":user_id" => $kullanici_id]);
                $firma_sonuc = $hazir_firma_sorgu->fetch(PDO::FETCH_ASSOC);

                if ($firma_sonuc && !empty($firma_sonuc['company_id'])) {
                    $firma_id = $firma_sonuc['company_id'];

                    $sorgu = 'SELECT id, code, discount, usage_limit, expire_date
                              FROM "Coupons"
                              WHERE company_id = :company_id
                              ORDER BY expire_date DESC';
                    $hazir_sorgu = $conn->prepare($sorgu);
                    $hazir_sorgu->execute([":company_id" => $firma_id]);
                    $kuponlar = $hazir_sorgu->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $kuponlar = [];
                }
                ?>
                <div class="firma">
                    <h2>Kupon Yönetimi</h2>

                    <div class="firmadüzen">
                        <div class="firmasol">
                            <h3>Kupon Ekle</h3>
                            <form class="firmaekle" method="POST">
                                <p>
                                    <label>İndirim Oranı:</label><br>
                                    <input type="number" name="indirimoran" required>
                                </p>

                                <p>
                                    <label>Kullanım Limiti:</label><br>
                                    <input type="number" name="kullanımlimit" required>
                                </p>

                                <p>
                                    <label>Son Kullanma Tarihi:</label><br>
                                    <input type="date" name="tarih" required>
                                </p>

                                <button type="submit" name="kuponekle">Kuponu Ekle</button>
                            </form>
                        </div>

                        <div class="firmasag">
                            <h3>Mevcut Kuponlar</h3>

                            <table class="firmatablo">
                                <thead>
                                    <tr>
                                        <th>Kupon Kodu</th>
                                        <th>İndirim Oranı</th>
                                        <th>Kullanım Limiti</th>
                                        <th>Son Kullanma Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($kuponlar) > 0): ?>
                                        <?php foreach ($kuponlar as $kupon): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($kupon['code']) ?></td>
                                                <td><?= htmlspecialchars($kupon['discount']) ?>%</td>
                                                <td><?= htmlspecialchars($kupon['usage_limit']) ?></td>
                                                <td><?= htmlspecialchars($kupon['expire_date']) ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="kuponsilid"
                                                            value="<?= htmlspecialchars($kupon['id']) ?>">
                                                        <button type="submit" name="kuponsil"
                                                            onclick="return confirm('Bu kuponu silmek istediğine emin misin?')">
                                                            <i class="fa-solid fa-trash"
                                                                style="color: #ffffff; font-size: 1.8rem;"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5">Henüz kupon bulunamadı.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php
            } elseif ($sayfa == 'yenisefer') {
                ?>
                <div class="yeni-sefer-formu">
                    <h2>Yeni Sefer Ekle</h2>

                    <form method="POST" action="seferkaydet.php" class="sefer-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="departure_city">Kalkış Şehri:</label>
                                <input type="text" id="departure_city" name="departure_city" required>
                            </div>

                            <div class="form-group">
                                <label for="destination_city">Varış Şehri:</label>
                                <input type="text" id="destination_city" name="destination_city" required>
                            </div>

                            <div class="form-group">
                                <label for="departure_time">Kalkış Zamanı:</label>
                                <input type="datetime-local" id="departure_time" name="departure_time" required>
                            </div>

                            <div class="form-group">
                                <label for="arrival_time">Varış Zamanı:</label>
                                <input type="datetime-local" id="arrival_time" name="arrival_time" required>
                            </div>

                            <div class="form-group">
                                <label for="price">Fiyat (TL):</label>
                                <input type="number" id="price" name="price" min="0" required>
                            </div>

                            <div class="form-group">
                                <label for="capacity">Kapasite:</label>
                                <input type="number" id="capacity" name="capacity" min="1" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-kaydet">Seferi Oluştur</button>
                    </form>
                </div>
                <?php
            }
            ?>
        </div>
    </section>
</body>

</html>
<?php
session_start();
include("../login_dosyalari/config.php");

function generate_uuid()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function kuponkoduoluştur($length = 10)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_company"])) {
    $company_name = trim($_POST["company_name"]);
    $logo_path = trim($_POST["logo_path"]);
    $company_admin_id = $_POST["company_admin_id"];

    if ($company_name === "" || empty($company_admin_id)) {
        $_SESSION['error_message'] = 'Firma adı ve admin seçimi zorunludur!';
        header("Location: admin.php?page=firmabtn");
        exit;
    }

    $conn->beginTransaction();
    $company_id = generate_uuid();

    $sql = 'INSERT INTO "Bus_Company" (id, name, logo_path) VALUES (:id, :name, :logo)';
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":id" => $company_id,
        ":name" => $company_name,
        ":logo" => $logo_path
    ]);

    $user_sql = "UPDATE \"User\" SET role = 'company', company_id = :company_id WHERE id = :user_id";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->execute([
        ":company_id" => $company_id,
        ":user_id" => $company_admin_id
    ]);

    $conn->commit();
    $_SESSION['success_message'] = 'Yeni firma başarıyla eklendi ve admin ataması yapıldı.';
    header("Location: admin.php?page=firmabtn");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_company"])) {
    $delete_id = $_POST["delete_company_id"];

    try {
        $conn->beginTransaction();

        $user_sql = 'UPDATE "User" SET role = "user", company_id = NULL 
                     WHERE company_id = :company_id AND role = "company"';
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->execute([":company_id" => $delete_id]);

        $sql = 'DELETE FROM "Bus_Company" WHERE id = :id';
        $stmt = $conn->prepare($sql);
        $stmt->execute([":id" => $delete_id]);

        $conn->commit();
        $_SESSION['success_message'] = '🗑️ Firma başarıyla silindi ve admin kullanıcısı user rolüne döndürüldü.';
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = '⚠️ Firma silinirken hata oluştu!';
    }

    header("Location: admin.php?page=firmabtn");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["kuponekle"])) {
    $kupon_id = generate_uuid();
    $kupon_kod = kuponkoduoluştur();
    $indirimoran = $_POST["indirimoran"];
    $company_id = 159753;
    $kullanımlimit = trim($_POST["kullanımlimit"]);
    $tarih = $_POST["tarih"];

    $sql = 'INSERT INTO "Coupons" 
            (id, code, discount, company_id, usage_limit, expire_date)
            VALUES (:id, :code, :discount, :company_id, :usage_limit, :expire_date)';
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":id" => $kupon_id,
        ":code" => $kupon_kod,
        ":discount" => $indirimoran,
        ":company_id" => $company_id,
        ":usage_limit" => $kullanımlimit,
        ":expire_date" => $tarih
    ]);

    $_SESSION['success_message'] = 'Kupon başarıyla eklendi.';
    header("Location: admin.php?page=kupon");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["kuponsil"])) {
    $kuponsil_id = $_POST["kuponsilid"];

    $conn->beginTransaction();
    $sql = 'DELETE FROM "Coupons" WHERE id = :id';
    $stmt = $conn->prepare($sql);
    $stmt->execute([":id" => $kuponsil_id]);
    $conn->commit();

    $_SESSION['success_message'] = 'Kupon başarıyla silindi.';
    header("Location: admin.php?page=kupon");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_role"])) {
    $user_id = $_POST["user_id"];
    $new_role = $_POST["new_role"];
    $company_id_value = ($new_role === 'company') ? null : null;

    $sql = 'UPDATE "User" SET role = :role, company_id = :company_id WHERE id = :id';
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":role" => $new_role,
        ":company_id" => $company_id_value,
        ":id" => $user_id
    ]);

    header("Location: admin.php?page=yonetim");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_user"])) {
    $delete_user_id = $_POST["delete_user_id"];

    try {
        $conn->beginTransaction();

        $check_sql = 'SELECT role, company_id FROM "User" WHERE id = :id';
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->execute([":id" => $delete_user_id]);
        $user = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['role'] === 'company' && !empty($user['company_id'])) {
            $company_sql = 'DELETE FROM "Bus_Company" WHERE id = :company_id';
            $company_stmt = $conn->prepare($company_sql);
            $company_stmt->execute([":company_id" => $user['company_id']]);
        }

        $sql = 'DELETE FROM "User" WHERE id = :id';
        $stmt = $conn->prepare($sql);
        $stmt->execute([":id" => $delete_user_id]);

        $conn->commit();
        $_SESSION['success_message'] = '🗑️ Kullanıcı başarıyla silindi.' .
            ($user && $user['role'] === 'company' ? ' İlgili firma da silindi.' : '');
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = '⚠️ Kullanıcı silinirken hata oluştu!';
    }

    header("Location: admin.php?page=yonetim");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | VINN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/admin_panel/admin.css">
</head>

<body>
    <header class="baslik">
        <a href="../index.php" class="Vınn">
            <img src="../images/logoblack.png" alt="">
        </a>

        <nav class="navbar">
            <a href="../index.php">
                <i class="fa-solid fa-home"></i> Ana Sayfa
            </a>
            <span class="navbar">|</span>

            <?php if (isset($_SESSION['user_name'])): ?>
                <a href="../index.php">
                    <i class="fa-solid fa-coins"></i>
                    Bakiye: <?= htmlspecialchars($_SESSION['balance']); ?>
                </a>
                <span class="navbar">|</span>
                <a href="../profil_islem/profile.php">
                    <i class="fa-solid fa-user"></i>
                    <?= htmlspecialchars($_SESSION['user_name']); ?>
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <section class="home">
        <div class="solbar">
            <div class="bilgi">
                <a href="/admin_panel/admin.php?page=dashboard"
                    class="<?php echo ($_GET['page'] ?? '') == 'dashboard' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-grip"></i>
                    <p>Dashboard</p>
                </a>
            </div>

            <div class="kullanici">
                <a href="/admin_panel/admin.php?page=yonetim"
                    class="<?php echo ($_GET['page'] ?? '') == 'yonetim' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-gear"></i>
                    <p>Yönetim</p>
                </a>
            </div>

            <div class="firmabtn">
                <a href="/admin_panel/admin.php?page=firmabtn"
                    class="<?php echo ($_GET['page'] ?? '') == 'firmabtn' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-bus"></i>
                    <p>Firmalar</p>
                </a>
            </div>

            <div class="kupon">
                <a href="/admin_panel/admin.php?page=kupon"
                    class="<?php echo ($_GET['page'] ?? '') == 'kupon' ? 'aktif' : ''; ?>">
                    <i class="fa-solid fa-ticket"></i>
                    <p>Kuponlar</p>
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

            $page = $_GET['page'] ?? 'dashboard';

            
            if ($page == 'dashboard') {
                $sql_user_count = 'SELECT COUNT(*) as count FROM "User"';
                $stmt_user = $conn->query($sql_user_count);
                $user_count = $stmt_user->fetch(PDO::FETCH_ASSOC)['count'];

                $sql_company_count = 'SELECT COUNT(*) as count FROM "Bus_Company"';
                $stmt_company = $conn->query($sql_company_count);
                $company_count = $stmt_company->fetch(PDO::FETCH_ASSOC)['count'];

                $sql_admin_count = 'SELECT COUNT(*) as count FROM "User" WHERE role = "admin"';
                $stmt_admin = $conn->query($sql_admin_count);
                $admin_count = $stmt_admin->fetch(PDO::FETCH_ASSOC)['count'];
                ?>
                <div class="kullancisayi">
                    <div class="yanyana">
                        <i class="fa-solid fa-user"></i>
                        <h2 class="bilgi">Kullanıcı Sayısı</h2>
                    </div>
                    <p class="yazi">Şu anda sistemde <?= $user_count ?> kullanıcı bulunmaktadır.</p>
                </div>

                <div class="sirketsayi">
                    <div class="yanyana">
                        <i class="fa-solid fa-bus"></i>
                        <h2 class="bilgi">Şirket Sayısı</h2>
                    </div>
                    <p class="yazi">Şu anda sistemde <?= $company_count ?> şirket bulunmaktadır.</p>
                </div>

                <div class="adminsayi">
                    <div class="yanyana">
                        <i class="fa-solid fa-user-tie" style="color: #000;"></i>
                        <h2 class="bilgi">Admin Sayısı</h2>
                    </div>
                    <p class="yazi">Şu anda sistemde <?= $admin_count ?> admin bulunmaktadır.</p>
                </div>

                <?php
                
            } elseif ($page == 'yonetim') {
                $sql = 'SELECT id, full_name, email, role FROM "User" ORDER BY created_at ASC';
                $stmt = $conn->query($sql);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="yonetim">
                    <h2>Kullanıcı Yönetimi</h2>
                    <table class="kullanici-tablo">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>E-posta</th>
                                <th>Mevcut Rol</th>
                                <th>Yeni Rol</th>
                                <th>Kaydet</th>
                                <th>Sil</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['full_name']); ?></td>
                                    <td><?= htmlspecialchars($u['email']); ?></td>
                                    <td><?= htmlspecialchars($u['role']); ?></td>
                                    <td>
                                        <form method="POST" style="display:flex; gap:0.5rem; justify-content:center;">
                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']); ?>">
                                            <select name="new_role">
                                                <option value="user" <?= $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="company" <?= $u['role'] === 'company' ? 'selected' : ''; ?>>Company
                                                </option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin
                                                </option>
                                            </select>
                                    </td>
                                    <td>
                                        <button type="submit" name="update_role">Kaydet</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="delete_user_id"
                                                value="<?= htmlspecialchars($u['id']); ?>">
                                            <button type="submit" name="delete_user" class="btnclr"
                                                onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">
                                                <i class="fa-solid fa-trash" style="color:#fff; font-size:1.8rem;"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
                
            } elseif ($page == 'firmabtn') {
                $sql = 'SELECT bc.id, bc.name, bc.logo_path, bc.created_at, u.full_name as admin_name 
                        FROM "Bus_Company" bc 
                        LEFT JOIN "User" u ON bc.id = u.company_id 
                        ORDER BY bc.created_at DESC';
                $stmt = $conn->query($sql);
                $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="firma">
                    <h2>Otobüs Firmaları Yönetimi</h2>
                    <div class="firmadüzen">
                        <div class="firmasol">
                            <h3>Firma Ekle</h3>
                            <form class="firmaekle" method="POST">
                                <p>
                                    <label>Firma Adı:</label><br>
                                    <input type="text" name="company_name" required>
                                </p>
                                <p>
                                    <label>Logo Yolu:</label><br>
                                    <input type="text" name="logo_path">
                                </p>
                                <p>
                                    <label>Firma Admini Seç:</label><br>
                                    <select name="company_admin_id" required>
                                        <option value="">Bir kişi seçiniz</option>
                                        <?php
                                        $user_sql = 'SELECT id, full_name FROM "User" WHERE role = "user" ORDER BY full_name';
                                        $user_stmt = $conn->query($user_sql);
                                        $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
                                        foreach ($users as $user): ?>
                                            <option value="<?= $user['id']; ?>"><?= htmlspecialchars($user['full_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <button type="submit" name="add_company">Firma Ekle</button>
                            </form>
                        </div>

                        <div class="firmasag">
                            <h3>Mevcut Firmalar</h3>
                            <table class="firmatablo">
                                <thead>
                                    <tr>
                                        <th>Firma Adı</th>
                                        <th>Firma Sahibi</th>
                                        <th>Logo</th>
                                        <th>Oluşturulma Tarihi</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($companies) > 0): ?>
                                        <?php foreach ($companies as $c): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($c['name']); ?></td>
                                                <td>
                                                    <?= !empty($c['admin_name']) ? htmlspecialchars($c['admin_name']) : '<span style="color:#999;">Atanmamış</span>'; ?>
                                                </td>
                                                <td><img src="<?= htmlspecialchars($c['logo_path']); ?>" alt="Logo"></td>
                                                <td><?= htmlspecialchars($c['created_at']); ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="delete_company_id"
                                                            value="<?= htmlspecialchars($c['id']); ?>">
                                                        <button type="submit" name="delete_company"
                                                            onclick="return confirm('Bu firmayı silmek istediğinize emin misiniz?')">
                                                            <i class="fa-solid fa-trash" style="color:#fff; font-size:1.8rem;"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5">Kayıtlı firma bulunamadı.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
                
            } elseif ($page == 'kupon') {
                $sql = 'SELECT id, code, discount, usage_limit, expire_date FROM "Coupons" WHERE company_id = 159753';
                $stmt = $conn->query($sql);
                $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                                    <input type="date" name="tarih">
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
                                    <?php if (count($coupons) > 0): ?>
                                        <?php foreach ($coupons as $c): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($c['code']); ?></td>
                                                <td><?= htmlspecialchars($c['discount']); ?>%</td>
                                                <td><?= htmlspecialchars($c['usage_limit']); ?></td>
                                                <td><?= htmlspecialchars($c['expire_date']); ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="kuponsilid"
                                                            value="<?= htmlspecialchars($c['id']); ?>">
                                                        <button type="submit" name="kuponsil"
                                                            onclick="return confirm('Bu kuponu silmek istediğinize emin misiniz?')">
                                                            <i class="fa-solid fa-trash" style="color:#fff; font-size:1.8rem;"></i>
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
            <?php } ?>
        </div>
    </section>
</body>

</html>
<?php

function uuid()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

include("config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $full_name = $_POST["adsoyad"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";



    if ($full_name === "" || $email === "" || $password === "") {
        echo "Lütfen tüm alanları doldurunuz.";
        exit;
    }


    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $user_id = uuid();
    $role = "user";





    $kontrol = "SELECT id FROM \"User\" WHERE email = :email";
    $kontrol_stmt = $conn->prepare($kontrol);
    $kontrol_stmt->execute([":email" => $email]);

    if ($kontrol_stmt->fetch()) {

        echo "<script>
        alert('Bu Eposta Zaten Kayıtlı!');
        window.location.href = '/index.php';
    </script>";
    } else
        $sql = "INSERT INTO \"User\" (id, full_name, email, role, password)
                VALUES (:id, :full_name, :email, :role, :password)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":id" => $user_id,
        ":full_name" => $full_name,
        ":email" => $email,
        ":role" => $role,
        ":password" => $hashed
    ]);
    echo "<script>
              alert('Kayıt Başarıyla Oluşturuldu!');
              window.location.href = '../index.php';
            </script>";



}
?>
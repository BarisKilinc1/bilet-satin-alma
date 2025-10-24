<?php
session_start();
include("config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        echo "<script>alert('Lütfen tüm alanları doldurunuz.');  window.location.href = '../index.php';</script>";
        exit;
    }

    
        
        
        $stmt = $conn->prepare('SELECT * FROM "User" WHERE email = :email');
        $stmt->execute([':email' => $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
         
    
        if ($user && password_verify($password, $user['password'])) {
           
            
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];     
            $_SESSION['user_role']  = $user['role']; 
            $_SESSION['balance'] = $user['balance'];
            
            echo "<script>
              alert('Giriş başarılı! Hoş geldin {$user['full_name']}');
              window.location.href = '../index.php';
            </script>";

        } else {
            
            echo "<script>
              alert('E-posta veya şifre hatalı!');
              window.location.href = '../index.php';
            </script>";
        }
}
?>

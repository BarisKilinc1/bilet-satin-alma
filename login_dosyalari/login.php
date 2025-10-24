<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/login_dosyalari/login.css">
</head>

<body>
    <div class="overlay"></div>
    <div class="popup popup-login">
        <div class="buton">&times;</div>
        <form class="form" method="POST" action="login_dosyalari/loginolma.php">
            <div class="form">
                <h2>Giriş Yap</h2>
                <div class="formstyle">
                    <label for="email">E-posta Adresi</label>
                    <input type="email" id="email" name="email" placeholder="E-posta adresinizi girin" required>
                </div>
                <div class="formstyle">
                    <label for="Password">Şifre</label>
                    <input type="password" id="password" name="password" placeholder="Şifrenizi girin" required>
                </div>
                <div class="formstyle">
                    <button>Giriş Yap</button>
                </div>
                
            </div>
        </form>
    </div>
    </div>

    <script src="../scripts/login.js"></script>
</body>

</html>
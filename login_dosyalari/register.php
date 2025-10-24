<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol | VINN</title>
    <link rel="stylesheet" href="/login_dosyalari/login.css">
</head>

<body>
    <div class="overlay"></div>
    <div class="popup popup-register">
        <div class="buton">&times;</div>
        <form class="form" method="POST" action="/login_dosyalari/kayitolma.php">
            <div class="form">
                <h2>Kayıt Ol</h2>
                
                
                <div class="formstyle">
                    <label >Ad Soyad</label>
                    <input type="text" id="adsoyad" name="adsoyad" placeholder="Ad Soyad" required>
                </div>
                <div class="formstyle">
                    <label >E-posta Adresi</label>
                    <input type="email" id="email" name="email" placeholder="E-posta adresinizi girin" required>
                </div>
                <div class="formstyle">
                    <label >Şifre</label>
                    <input type="password" id="password" name="password" placeholder="Şifrenizi Girin" required minlength="6">
                </div>

                <div class="formstyle">
                    <button>Kayıt Ol</button>
                </div>
                <div class="formstyle">
                    <a href="">Zaten Hesabın Var mı Giriş Yap</a>
                </div>
                

            </div>
        </form>
    </div>
    </div>
    
    <script src="../scripts/login.js"></script>
</body>

</html>
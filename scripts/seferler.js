document.querySelectorAll('.koltuk-sec-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const seferId = btn.getAttribute('data-sefer');
    const detayDiv = document.querySelector(`.sefer-detay[data-id="${seferId}"]`);
    if (!detayDiv) return;

    detayDiv.classList.toggle('goster');

    const selectKoltuk = detayDiv.querySelector('select');
    const satinalBtn = detayDiv.querySelector('.satinal-btn');
    let seciliKoltuk = null;

    if (satinalBtn) satinalBtn.disabled = true;

    if (selectKoltuk) {
      selectKoltuk.addEventListener('change', () => {
        seciliKoltuk = selectKoltuk.value;
        satinalBtn.disabled = seciliKoltuk === "";
      });
    }

    if (satinalBtn) {
      satinalBtn.addEventListener('click', (e) => {

       
        if (!giris) {
          e.preventDefault();
          alert("Lütfen önce giriş yapın.");
          window.location.href = "../index.php";
          return;
        }

       
        if (!seciliKoltuk || seciliKoltuk === "") {
          alert("Lütfen bir koltuk seçin!");
          return;
        }

       
        window.location.href = `/satinal_islem/satinal.php?sefer=${seferId}&koltuk=${seciliKoltuk}`;
      });
    }
  });
});



document.addEventListener("DOMContentLoaded", () => {
  const kuponBtn = document.querySelector(".kupon-uygula");
  const kuponInput = document.querySelector("input[name='kupon_kodu']");

  if (kuponBtn) {
    kuponBtn.addEventListener("click", () => {
      const kupon = kuponInput.value.trim();
      if (kupon === "") {
        alert("Lütfen bir kupon kodu girin!");
        return;
      }

      fetch("odeme-islem.php", {
        method: "POST",
        body: new URLSearchParams({
          kupon_kodu: kupon,
          sefer_id: document.querySelector("input[name='sefer_id']").value,
          koltuk_no: document.querySelector("input[name='koltuk_no']").value,
          toplam_fiyat: document.querySelector("input[name='toplam_fiyat']").value
        })
      }).then(() => {
        alert(`Kupon "${kupon}" başarıyla uygulandı!`);
      });
    });
  }
});

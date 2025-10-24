document.addEventListener("DOMContentLoaded", function () {
  const sifreBtn = document.getElementById("sifreDegistirBtn");
  const sifreForm = document.getElementById("sifreDegistirForm");
  const iptalBtn = document.getElementById("iptalBtn");

  sifreForm.classList.remove("show");

  if (sifreBtn && sifreForm) {
    sifreBtn.addEventListener("click", function () {
      sifreForm.classList.toggle("show");
    });
  }

  if (iptalBtn && sifreForm) {
    iptalBtn.addEventListener("click", function () {
      sifreForm.classList.remove("show");
      const form = sifreForm.querySelector("form");
      if (form) form.reset();
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const hesapSilBtn = document.getElementById("hesapSilBtn");
  const hesapSilForm = document.getElementById("hesapSilForm");

  if (hesapSilBtn && hesapSilForm) {
    hesapSilBtn.addEventListener("click", function () {
      const userConfirmed = confirm(
        " HESAP SİLME UYARISI!\n\n" +
          "Hesabınızı silmek istediğinizden emin misiniz?\n" +
          "Bu işlem geri alınamaz!\n\n" +
          "Tüm verileriniz kalıcı olarak silinecektir."
      );

      if (userConfirmed) {
        hesapSilForm.submit();
      }
    });
  }
});
document.addEventListener("DOMContentLoaded", function () {
  const pdfBtns = document.querySelectorAll(".pdf-btn");

  if (pdfBtns.length > 0) {
    pdfBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const printContent = document.querySelector(".biletler").innerHTML;
        const newWindow = window.open("", "_blank");
        newWindow.document.write(`
          <html>
            <head>
              <title>Biletlerim</title>
              <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 8px; text-align: center; }
                th { background-color: #05214a; color: white; }
                h2 { text-align: center; }
              </style>
            </head>
            <body>
              ${printContent}
            </body>
          </html>
        `);
        newWindow.document.close();
        newWindow.print();
      });
    });
  }
});

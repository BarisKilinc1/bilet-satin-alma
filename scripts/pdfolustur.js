window.addEventListener("load", () => {
  if (!window.jspdf) {
    alert("PDF kütüphanesi yüklenemedi!");
    return;
  }

  const { jsPDF } = window.jspdf;

  document.querySelectorAll(".pdf-btn").forEach(btn => {
    btn.addEventListener("click", async () => {
      const doc = new jsPDF({
        orientation: "landscape",
        unit: "mm",
        format: "a5",
      });

     
      doc.setFillColor(245, 245, 245);
      doc.rect(0, 0, 210, 148, "F");

      
      doc.setFillColor(0, 0, 0);
      doc.rect(0, 0, 15, 148, "F");

      
      try {
        const logo = await fetch("/images/logoblack.png");
        const blob = await logo.blob();
        const base64 = await blobToBase64(blob);
       
        doc.addImage(base64, "PNG", 20, 8, 55, 25);
      } catch (e) {
        console.log("Logo yüklenemedi:", e);
      }

      
      doc.setFont("helvetica", "bold");
      doc.setFontSize(24);
      doc.setTextColor(0, 0, 0); 
      doc.text("VINN Biletiniz", 150, 22, { align: "center" });

    
      const guzergah = btn.dataset.sefer;
      const tarih = btn.dataset.tarih;
      const koltuk = btn.dataset.koltuk;
      const fiyat = btn.dataset.fiyat;

   
      doc.setDrawColor(180, 180, 180);
      doc.setLineWidth(0.4);
      doc.rect(20, 40, 170, 85);

      doc.setFont("helvetica", "bold");
      doc.setFontSize(14);
      doc.setTextColor(0, 0, 0);
      doc.text("Güzergah:", 30, 60);
      doc.text("Tarih:", 30, 78);
      doc.text("Koltuk No:", 30, 96);
      doc.text("Fiyat:", 30, 114);

    
      doc.setFont("helvetica", "normal");
      doc.text(guzergah, 80, 60);
      doc.text(tarih, 80, 78);
      doc.text(koltuk, 80, 96);
      doc.text(fiyat, 80, 114);

  
    
      doc.save(`VINN_Bilet_${guzergah.replace(/ /g, "_")}.pdf`);
    });
  });

  
  function blobToBase64(blob) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }
});

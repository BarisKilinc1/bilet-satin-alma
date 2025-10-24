const degistir = document.getElementById("degistir");
const nereden = document.getElementById("nereden");
const nereye = document.getElementById("nereye");
let temp;

degistir.addEventListener("click", () => {
  temp = nereden.value;
  nereden.value = nereye.value;
  nereye.value = temp;
});


document.addEventListener("DOMContentLoaded", function() {
  const tarihInput = document.getElementById("tarih");
  if (tarihInput) {
    const today = new Date().toISOString().split("T")[0];
    tarihInput.min = today; 
  }
});

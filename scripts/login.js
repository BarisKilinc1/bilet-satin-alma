
document.querySelector("#login").addEventListener("click", function (e) {
  e.preventDefault();
  document.querySelector(".popup-login").classList.add("active"); 
  document.querySelector(".overlay").classList.add("active");
  document.body.classList.add("no-scroll");
});


document.querySelector(".popup-login .buton").addEventListener("click", function () {
  document.querySelector(".popup-login").classList.remove("active");
  document.querySelector(".overlay").classList.remove("active");
  document.body.classList.remove("no-scroll");
});


document.querySelector(".overlay").addEventListener("click", function () {
  document.querySelectorAll(".popup").forEach(p => p.classList.remove("active")); 
  this.classList.remove("active");
  document.body.classList.remove("no-scroll");
});


document.querySelector("#register").addEventListener("click", function (e) {
  e.preventDefault();
  document.querySelector(".popup-register").classList.add("active"); 
  document.querySelector(".overlay").classList.add("active");
  document.body.classList.add("no-scroll");
});


document.querySelector(".popup-register .buton").addEventListener("click", function () {
  document.querySelector(".popup-register").classList.remove("active");
  document.querySelector(".overlay").classList.remove("active");
  document.body.classList.remove("no-scroll");
});

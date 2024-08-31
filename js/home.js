document.addEventListener("DOMContentLoaded", verificarAutenticacao);
function callBackAutenticacao(dadosAuth) {
  fetch("pages/header.html")
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("header-placeholder").innerHTML = data;
    });
}

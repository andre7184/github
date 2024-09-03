function verificarAutenticacao() {
  const ajaxRequest = new AjaxRequest("pages/remote.php");
  ajaxRequest
    .send({ acao: "dados_do_usuario" })
    .then((data) => {
      if (data.naoautenticado) {
        // retornar para a pagina de
        window.location.href = "index.html";
      } else {
        callBackAutenticacao(data);
      }
    })
    .catch((error) => console.error("Erro:", error));
}
function logout() {
  const ajaxRequest = new AjaxRequest("pages/remote.php");
  ajaxRequest
    .send({ acao: "logout" })
    .then((data) => {
      window.location.href("index.html");
    })
    .catch((error) => console.error("Erro:", error));
}

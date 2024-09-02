document.getElementById("login-github").addEventListener("click", function () {
  const clientId = "Ov23litmC5N7kJpBy26i"; // Substitua pelo seu Client ID do GitHub
  const redirectUri =
    "https://vps52814.publiccloud.com.br/github/pages/remote.php?acao=authgithub"; // Substitua pela URL de redirecionamento do seu site
  const state = Math.random().toString(36).substring(7); // Gera um estado aleatório para segurança
  const data = {
    state: state,
    acao: "salvar_stategithub",
  };

  const ajaxRequest = new AjaxRequest("pages/remote.php");
  ajaxRequest
    .send(data)
    .then((data) => {
      if (data.success) {
        const authUrl = `https://github.com/login/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&state=${state}&scope=user:email`;
        window.location.href = authUrl;
      } else {
        feedback.textContent = "Não foi possível realizar o login por GitHub";
        feedback.className = "error";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
});

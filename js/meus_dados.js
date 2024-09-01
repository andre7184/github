document.addEventListener("DOMContentLoaded", verificarAutenticacao);

function callBackAutenticacao(dadosAuth) {
  fetch("pages/header.html")
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("header-placeholder").innerHTML = data;
    });

  if (dadosAuth) {
    document.getElementById("username").textContent = dadosAuth.user.user;
    document.getElementById("email").textContent = dadosAuth.user.email;
  }
}

function showAlterarSenhaPopup() {
  const message = `
      <form id="alterarSenhaForm" class="form">
        <p class="form-title"><b>Alterar Senha</b></p>
        <div class="input-container">
          <label for="senhaAtual">Senha Atual:</label>
          <input type="password" id="senhaAtual" name="senhaAtual" required />
        </div>
        <div class="input-container">
          <label for="novaSenha">Nova Senha:</label>
          <input type="password" id="novaSenha" name="novaSenha" required />
        </div>
        <div class="input-container">
          <label for="confirmarSenha">Confirmar Nova Senha:</label>
          <input type="password" id="confirmarSenha" name="confirmarSenha" required />
        </div>
        <button type="button" onclick="alterarSenha()" class="submit">Confirmar</button>
      </form>
    `;
  showPopup("form", message);
}

function showAlterarEmailPopup() {
  const message = `
      <form id="alterarEmailForm" class="form">
        <p class="form-title"><b>Alterar Email</b></p>
        <div class="input-container">
          <label for="novoEmail">Novo Email:</label>
          <input type="email" id="novoEmail" name="novoEmail" required />
        </div>
        <button type="button" onclick="alterarEmail()" class="submit">Confirmar</button>
      </form>
    `;
  showPopup("form", message);
}

function alterarSenha() {
  const senhaAtual = document.getElementById("senhaAtual").value;
  const novaSenha = document.getElementById("novaSenha").value;
  const confirmarSenha = document.getElementById("confirmarSenha").value;

  if (novaSenha !== confirmarSenha) {
    alert("As senhas não coincidem.");
    return;
  }

  const data = {
    acao: "alterar_senha",
    senhaAtual: senhaAtual,
    novaSenha: novaSenha,
  };

  const ajaxRequest = new AjaxRequest("pages/remote.php");
  ajaxRequest
    .send(data)
    .then((response) => {
      alert("Senha alterada com sucesso.");
      hidePopup();
    })
    .catch((error) => {
      console.error("Erro ao alterar senha:", error);
    });
}

function alterarEmail() {
  const novoEmail = document.getElementById("novoEmail").value;

  const data = {
    acao: "alterar_email",
    novoEmail: novoEmail,
  };

  const ajaxRequest = new AjaxRequest("pages/remote.php");
  ajaxRequest
    .send(data)
    .then((response) => {
      alert("Email alterado com sucesso.");
      hidePopup();
    })
    .catch((error) => {
      console.error("Erro ao alterar email:", error);
    });
}

function removerConta() {
  if (confirm("Tem certeza que deseja remover sua conta?")) {
    const ajaxRequest = new AjaxRequest("pages/remote.php");
    ajaxRequest
      .send({ acao: "remover_conta" })
      .then((response) => {
        alert("Conta removida com sucesso.");
        window.location.href = "login.html";
      })
      .catch((error) => {
        console.error("Erro ao remover conta:", error);
      });
  }
}

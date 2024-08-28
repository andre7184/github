document.getElementById("username").addEventListener("input", function () {
  const username = this.value;
  const feedback = document.getElementById("usernameFeedback");

  if (username.length < 3) {
    feedback.textContent = "O nome de usuário deve ter no mínimo 3 caracteres.";
    feedback.className = "error";
    return;
  }

  const ajax = new AjaxRequest("usuario.php");
  ajax
    .send({ username: username, acao: "verifica_nomeusuario" })
    .then((data) => {
      if (data.exists) {
        feedback.textContent = "Nome de usuário já está em uso.";
        feedback.className = "error";
      } else {
        feedback.textContent = "Nome de usuário disponível.";
        feedback.className = "success";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      feedback.textContent = "Erro ao verificar nome de usuário.";
      feedback.className = "error";
    });
});

document
  .getElementById("cadastroForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    const data = {
      username: username,
      email: email,
      password: password,
      acao: "cadastrar_usuario",
    };

    const ajax = new AjaxRequest("usuario.php");
    ajax
      .send(data)
      .then((data) => {
        console.log("Success:", data);
        alert("Cadastro realizado com sucesso!");
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Ocorreu um erro ao realizar o cadastro.");
      });
  });

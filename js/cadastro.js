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
document.getElementById("user_gh").addEventListener("input", function () {
  const username = this.value;
  const imgGithub = document.getElementById("imgGithub");

  if (username) {
    fetch(`https://api.github.com/users/${username}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.avatar_url) {
          imgGithub.innerHTML = `<img src="${data.avatar_url}" alt="Avatar do GitHub" width="50" height="50">`;
        } else {
          imgGithub.innerHTML = "Usuário não encontrado";
        }
      })
      .catch((error) => {
        imgGithub.innerHTML = "Erro ao buscar usuário";
        console.error("Erro:", error);
      });
  } else {
    imgGithub.innerHTML = "";
  }
});

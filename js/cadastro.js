document
  .getElementById("cadastroForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const username = document.getElementById("user_gh").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    const data = {
      user: username,
      email: email,
      senha: password,
      acao: "cadastrar_usuario",
    };

    const ajax = new AjaxRequest("pages/remote.php");
    ajax
      .send(data)
      .then((data) => {
        if (data.authenticado) {
          window.location.href = "home.html";
        } else if (data.status) {
          if (data.status == "success") {
            window.location.replace("login.html");
          }
        }
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
          verificaUser(username);
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
function verificaUser(username) {
  console.log(username);
  const feedback = document.getElementById("usernameFeedback");
  const ajax = new AjaxRequest("pages/remote.php");
  ajax
    .send({ user: username, acao: "verifica_user" })
    .then((data) => {
      if (data.status) {
        if (data.status == "error") {
          feedback.textContent = "Nome de usuário já está em uso.";
          feedback.className = "error";
        } else {
          feedback.textContent = "Nome de usuário disponível.";
          feedback.className = "success";
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      feedback.textContent = "Erro ao verificar nome de usuário.";
      feedback.className = "error";
    });
}

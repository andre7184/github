document
  .getElementById("loginForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const data = {
      username: username,
      password: password,
      acao: "login",
    };

    const ajax = new AjaxRequest("usuario.php");
    ajax
      .send(data)
      .then((data) => {
        if (data.success) {
          alert("Login realizado com sucesso!");
          // Redirecionar para a página do usuário
          window.location.href = "home.html";
        } else {
          alert("Nome de usuário ou senha incorretos.");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Ocorreu um erro ao realizar o login.");
      });
  });

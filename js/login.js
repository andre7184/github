document
  .getElementById("loginForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value;
    const senha = document.getElementById("senha").value;

    const data = {
      email: email,
      senha: senha,
      acao: "fazer_login",
    };
    const feedback = document.getElementById("loginFeedback");
    const ajax = new AjaxRequest("pages/remote.php");
    ajax
      .send(data)
      .then((data) => {
        if (data.success) {
          window.location.replace("home.html");
        } else {
          feedback.textContent = data.message;
          feedback.className = "error";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Ocorreu um erro ao realizar o login.");
      });
  });

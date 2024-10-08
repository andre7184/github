document.addEventListener("DOMContentLoaded", verificarAutenticacao);
function callBackAutenticacao(dadosAuth) {
  fetch("pages/header.html")
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("header-placeholder").innerHTML = data;
    });
  if (dadosAuth) {
    document.getElementById("username").textContent = dadosAuth.user.user;
    carregarRepositorios(dadosAuth);
  }
}
function carregarRepositorios(dados) {
  console.log(dados);
  const username = dados.user.user;
  const dados_diretorios = dados.diretorios;

  // Adicionando a URL do avatar
  fetch(`https://api.github.com/users/${username}`, {
    headers: {
      "User-Agent": "Mozilla/5.0",
    },
  })
    .then((response) => response.json())
    .then((user) => {
      const avatar = document.getElementById("avatar");
      avatar.src = user.avatar_url;
    })
    .catch((error) => console.error("Erro ao carregar avatar:", error));

  fetch(`https://api.github.com/users/${username}/repos`, {
    headers: {
      "User-Agent": "Mozilla/5.0",
    },
  })
    .then((response) => response.json())
    .then((repos) => {
      const repoList = document.getElementById("repo-list");
      repoList.innerHTML = ""; // Limpa a lista antes de adicionar novos itens

      repos.forEach((repo) => {
        const listItem = document.createElement("li");
        const diretorio = dados_diretorios.find((d) => d.nome === repo.name);

        // Verificar se a linguagem do repositório é uma linguagem web
        const webLanguages = [
          "HTML",
          "CSS",
          "JavaScript",
          "PHP",
          "Ruby",
          "Python",
          "TypeScript",
        ];
        const isWebRepo = webLanguages.includes(repo.language);
        const linguagem = repo.language.toLowerCase();
        const iconeLinguagem = `imgs/${linguagem}.png`;

        listItem.innerHTML = `
          <div class="repo-info">
            <strong>${repo.name}</strong><br>
            Linguagem: <img src="${iconeLinguagem}" alt="${
          repo.language
        }" style="width: 20px; height: 20px;">${repo.language}<br>
            <a href="${repo.html_url}" target="_blank">Ver no GitHub</a>
          </div>
          <div class="repo-action">
            ${
              diretorio
                ? `<button class="button-ver-diretorio" onclick="abrirDiretorio(${diretorio.id})">Ver Diretório</button>`
                : isWebRepo
                ? `<button class="button-clonar-repositorio" onclick="clonarRepositorio('${repo.name}')">Clonar Repositório</button>`
                : ""
            }
          </div>
        `;
        repoList.appendChild(listItem);
      });
    })
    .catch((error) => console.error("Erro:", error));
}

function clonarRepositorio(repoNome) {
  const data = {
    acao: "clonar_repositorio",
    repositorio: repoNome,
  };
  const ajaxRequest = new AjaxRequest("pages/remote.php");
  ajaxRequest
    .send(data)
    .then((response) => {
      console.log("Resposta do servidor:", response);
      verificarAutenticacao();
    })
    .catch((error) => {
      console.error("Erro ao clonar repositório:", error);
    });
}

function abrirDiretorio(id) {
  localStorage.setItem("id_diretorio", id);
  window.location.href = "diretorios.html";
}

document.addEventListener("DOMContentLoaded", verificarAutenticacao);

function callBackAutenticacao(dadosAuth) {
  fetch("pages/header.html")
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("header-placeholder").innerHTML = data;
    });
  carregarDiretorios();
}
function carregarDiretorios() {
  const ajaxRequest = new AjaxRequest("pages/remote.php");
  const id_diretorio = localStorage.getItem("id_diretorio");
  let data;
  if (id_diretorio !== null) {
    data = { acao: "listar_repositorios", id_diretorio: id_diretorio };
    localStorage.removeItem("id_diretorio");
  } else {
    data = { acao: "listar_repositorios" };
  }

  ajaxRequest
    .send(data)
    .then((data) => {
      const diretoriosGrid = document.getElementById("diretorios-grid");
      diretoriosGrid.innerHTML = ""; // Limpa a grid antes de adicionar novos itens
      if (data.repositorios) {
        data.repositorios.forEach((diretorio) => {
          const card = document.createElement("div");
          card.className = "diretorio-card";
          const linguagem = diretorio.linguagem.toLowerCase();
          const iconeLinguagem = `imgs/${linguagem}.png`;

          card.innerHTML = `
              <h2>${diretorio.nome}</h2>
              <p>ID: ${diretorio.id}</p>
              <p>Data Atualizado: ${diretorio.data_atualizado}</p>
              <p>Linguagem: <img src="${iconeLinguagem}" alt="${diretorio.linguagem}" style="width: 20px; height: 20px;"> ${diretorio.linguagem}</p>
              <a href="${diretorio.url}" target="_blank">Acessar Diretório</a>
              <div class="buttons">
                <button class="button" onclick="atualizarDiretorio('${diretorio.id}')">Atualizar</button>
                <button class="button button-remover" onclick="removerDiretorio('${diretorio.id}')">Remover</button>
              </div>
            `;

          diretoriosGrid.appendChild(card);
        });
      }
    })
    .catch((error) => {
      console.error("Erro ao carregar diretórios:", error);
    });
}

function atualizarDiretorio(id) {
  const data = {
    acao: "atualizar_repositorio",
    id_diretorio: id,
  };
  const ajaxRequest = new AjaxRequest("pages/remote.php");

  ajaxRequest
    .send(data)
    .then((response) => {
      console.log("Diretório atualizado:", response);
      carregarDiretorios(); // Recarrega a lista de diretórios
    })
    .catch((error) => {
      console.error("Erro ao atualizar diretório:", error);
    });
}

function removerDiretorio(id) {
  const data = {
    acao: "remover_repositorio",
    id_diretorio: id,
  };
  const ajaxRequest = new AjaxRequest("pages/remote.php");

  ajaxRequest
    .send(data)
    .then((response) => {
      console.log("Diretório removido:", response);
      carregarDiretorios(); // Recarrega a lista de diretórios
    })
    .catch((error) => {
      console.error("Erro ao remover diretório:", error);
    });
}

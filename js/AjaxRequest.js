class AjaxRequest {
  constructor(url) {
    this.url = url;
  }
  send(data) {
    // Cria um objeto FormData
    let formData = new FormData();

    // Adiciona os dados ao objeto FormData
    for (let key in data) {
      if (data[key] instanceof File) {
        formData.append(key, data[key]); // formnato para envio de arquivos
      } else if (typeof data[key] === "object") {
        formData.append(key, JSON.stringify(data[key])); // formnato para envio de json
      } else {
        formData.append(key, data[key]); // formnato para envio de html
      }
    }

    // Faz a solicitação POST
    return fetch(this.url, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text().then((text) => {
          return text ? JSON.parse(text) : {}; // Retorna um objeto vazio se a resposta estiver vazia
        });
        //return response.json(); // ou response.text() se a resposta não for JSON
      })
      .catch((error) => {
        console.error("Erro:", error);
      });
  }
}
function showPopup(type, message) {
  // Remove o elemento anterior, se existir
  var oldPopup = document.querySelector(".popup-message");
  if (oldPopup) oldPopup.remove();

  // Cria os elementos
  var popup = document.createElement("div");
  var popupIcon = document.createElement("div");
  var img = document.createElement("img");
  var msg = document.createElement("div");
  var closeIcon = document.createElement("div");
  var btn = document.createElement("button");

  // Define as classes e atributos
  popup.className = "popup-message";
  popupIcon.className = "popup-icon";
  img.src = "imgs/" + type + ".svg";
  msg.className = "message";
  closeIcon.className = "close-icon";
  btn.setAttribute("onclick", "hidePopup()");
  btn.textContent = "X";

  // Adiciona os elementos ao DOM
  closeIcon.appendChild(btn);
  popupIcon.appendChild(img);
  popup.appendChild(popupIcon);
  popup.appendChild(msg);
  popup.appendChild(closeIcon);
  document.body.appendChild(popup);

  // Adiciona a classe correta ao popup
  if (type == "sucess") {
    popup.classList.add("agreen");
  } else if (type == "error") {
    popup.classList.add("ared");
  } else if (type == "alert") {
    popup.classList.add("aorange");
  } else if (type == "load") {
    popup.classList.add("aload");
  } else if (type == "form") {
    popup.classList.add("question");
  } else {
    popup.classList.add("ablue");
  }

  // Define a mensagem correta
  if (type == "load") {
    if (!message) {
      message = "Carregando...";
    }
    msg.innerHTML =
      '<div style="display: flex; align-items: center;"><span>' +
      message +
      '..</span><div class="loading"></div></div>';
    closeIcon.style.display = "none"; // Esconde o botão de fechar
  } else if (type == "form") {
    // inserir dentro da variavel message um input com o texto de quantidade e um botão de confirmar
    msg.innerHTML = message;
    closeIcon.style.display = "block";
  } else {
    msg.textContent = message;
    closeIcon.style.display = "block";
  }

  // Mostra o popup
  popup.style.display = "flex";
}

function hidePopup() {
  document.querySelector(".popup-message").style.display = "none";
  document.querySelector(".overlay").style.display = "none";
}

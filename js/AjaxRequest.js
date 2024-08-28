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

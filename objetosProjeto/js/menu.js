function gravarPlaca() {
    let placa = document.getElementById("placa").value;
    let usuario_id = document.getElementById("usuario_id").value;

    fetch("php/cadastrarPlaca.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ placa: placa, usuario_id: usuario_id })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Erro na resposta do servidor: " + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("Resposta do servidor:", data);
        alert(data.mensagem);
    })
    .catch(error => {
        console.error("Erro ao enviar os dados:", error);
        alert("Erro ao cadastrar veículo. Verifique a conexão.");
    });
}

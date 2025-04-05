function abrirTela(event) {
    const elementoClicado = event.target.id;
    const conteudo = document.getElementById("conteudo");

    switch (elementoClicado) {
        case "tela_inicial":
            conteudo.innerHTML = `
                <h2>Página Inicial</h2>
                <p>Bem-vindo ao VagaXpress! Escolha uma opção no menu lateral.</p>
            `;
            break;

        case "cadastro_veiculos":
            conteudo.innerHTML = `
                <h2>Cadastro de Veículo</h2>
                <form onsubmit="event.preventDefault(); gravarPlaca();">
                    <input id="placa" placeholder="Placa" required>
                    <input id="usuario_id" type="hidden" value="1">
                    <button type="submit">Cadastrar</button>
                </form>
            `;
            break;

        case "agendamento":
            conteudo.innerHTML = `
                <h2>Agendamento de Estacionamento</h2>
                <form onsubmit="event.preventDefault();">
                    <input placeholder="Placa" required>
                    <input type="date" required>
                    <input type="time" required>
                    <button type="submit">Agendar</button>
                </form>
            `;
            break;

        case "perfil_usuario":
            conteudo.innerHTML = `
            <h2>Perfil do usuário</h2>
            <p>Veja seu perfil aqui.</p>
        `;
        break;

        case "notificacao":
            conteudo.innerHTML = `
                <h2>Notificações</h2>
        ;`
            carregarNotificacoes();

        case "suporte":
            conteudo.innerHTML = `
                <h2>Suporte</h2>
                <p>Entre em contato com o suporte se precisar de ajuda.</p>
            `;
            break;
            
        default:
            conteudo.innerHTML = "<h2>Bem-vindo ao VagaXpress</h2>";
    }
}

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

function carregarNotificacoes() {
    fetch("php/busca_notificacoes.php")
        .then(response => response.json())
        .then(data => {
            const conteudo = document.getElementById("conteudo");
            let tabela = `
                <h2>Notificações</h2>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mensagem</th>
                        </tr>
                    </thead>
                    <tbody>`;

            data.forEach(notificacao => {
                tabela += `
                    <tr>
                        <td>${notificacao.idMensagem}</td>
                        <td>${notificacao.mensagem}</td>
                    </tr>`;
            });

            tabela += `</tbody></table>`;
            conteudo.innerHTML = tabela;
        })
        .catch(error => {
            console.error("Erro ao carregar notificações:", error);
            document.getElementById("conteudo").innerHTML = "<p>Erro ao carregar notificações.</p>";
        });
}

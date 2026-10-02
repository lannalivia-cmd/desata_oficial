document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("node-form");
    const toast = document.getElementById("toast");

    if (!form) return;

    form.addEventListener("submit", async (event) => {

        event.preventDefault();

        const botao = form.querySelector(".btn-submit");

        // Pega os valores do formulário
        const titulo = form.querySelector("input[name='titulo']").value.trim();
        const cidadeEstado = form.querySelector("input[name='cidade_estado']").value.trim();
        const areaId = form.querySelector("select[name='area_id']").value;
        const publicoAlvo = form.querySelector("input[name='publico_alvo']").value.trim();
        const descricao = form.querySelector("textarea[name='descricao']").value.trim();

        if (!titulo || !cidadeEstado || !areaId || !descricao) {
            mostrarToast("Preencha todos os campos obrigatórios.", "erro");
            return;
        }

        // Separa "Cidade, SP"
        const partes = cidadeEstado.split(",");

        const cidade = partes[0].trim();
        const estado = partes[1] ? partes[1].trim().toUpperCase() : "";

        if (!cidade || estado.length !== 2) {
            mostrarToast("Digite a cidade e o estado no formato: Cidade, SP.", "erro");
            return;
        }

        // ID temporário para teste.
        // Depois vamos substituir pelo ID do usuário logado.
        const usuarioId = 1;

        const dados = new FormData();

        dados.append("usuario_id", usuarioId);
        dados.append("area_id", areaId);
        dados.append("titulo", titulo);
        dados.append("cidade", cidade);
        dados.append("estado", estado);
        dados.append("publico_alvo", publicoAlvo);
        dados.append("descricao", descricao);

        botao.disabled = true;
        botao.textContent = "Publicando...";

        try {

            const resposta = await fetch("config/salvar-no.php", {
                method: "POST",
                body: dados
            });

            const resultado = await resposta.json();

            if (resultado.sucesso) {

                mostrarToast("Nó publicado com sucesso!", "sucesso");

                form.reset();

                setTimeout(() => {
                    window.location.href = "mural.html";
                }, 1500);

            } else {

                console.error(resultado);

                mostrarToast(
                    resultado.erro || "Não foi possível salvar o nó.",
                    "erro"
                );
            }

        } catch (erro) {

            console.error("Erro:", erro);

            mostrarToast(
                "Erro na conexão ao salvar o nó.",
                "erro"
            );

        } finally {

            botao.disabled = false;
            botao.textContent = "Publicar Nó Comunitário";
        }
    });


    function mostrarToast(mensagem, tipo) {

        if (!toast) {
            alert(mensagem);
            return;
        }

        toast.textContent = mensagem;
        toast.className = "toast " + tipo;

        toast.classList.add("show");

        setTimeout(() => {
            toast.classList.remove("show");
        }, 4000);
    }

});
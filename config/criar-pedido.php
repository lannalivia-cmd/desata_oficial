<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Método não permitido.'
        ]);

        exit;
    }


    $no_id = filter_input(
        INPUT_POST,
        'no_id',
        FILTER_VALIDATE_INT
    );


    if (!$no_id) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Nó não informado.'
        ]);

        exit;
    }


    /*
     * Pega o professor que está logado.
     */

    $professor_id =
        $_SESSION['usuario_id'] ?? null;


    if (!$professor_id) {

        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Professor não está logado.'
        ]);

        exit;
    }


    /*
     * Confirma que o usuário é professor/admin.
     */

    $stmt = $pdo->prepare("
        SELECT id, tipo, ativo
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $professor_id
    ]);

    $professor = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (
        !$professor ||
        !$professor['ativo'] ||
        (
            $professor['tipo'] !== 'professor' &&
            $professor['tipo'] !== 'admin'
        )
    ) {

        http_response_code(403);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Usuário não autorizado.'
        ]);

        exit;
    }


    /*
     * Verifica se o nó existe e está aberto.
     */

    $stmt = $pdo->prepare("
        SELECT id, status
        FROM nos
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $no_id
    ]);

    $no = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if (!$no) {

        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Nó não encontrado.'
        ]);

        exit;
    }


    if ($no['status'] !== 'aberto') {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Este nó já está sendo acompanhado.'
        ]);

        exit;
    }


    /*
     * Verifica se já existe pedido.
     */

    $stmt = $pdo->prepare("
        SELECT id
        FROM pedidos_ajuda
        WHERE no_id = :no_id
        LIMIT 1
    ");

    $stmt->execute([
        ':no_id' => $no_id
    ]);


    $pedidoExistente = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    if ($pedidoExistente) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Este nó já possui um atendimento.'
        ]);

        exit;
    }


    /*
     * Cria o pedido.
     */

    $stmt = $pdo->prepare("
        INSERT INTO pedidos_ajuda
        (
            no_id,
            professor_id,
            mensagem,
            status
        )
        VALUES
        (
            :no_id,
            :professor_id,
            :mensagem,
            'aceito'
        )
    ");


    $stmt->execute([

        ':no_id' => $no_id,

        ':professor_id' => $professor_id,

        ':mensagem' =>
            'Atendimento assumido pelo professor.'

    ]);


    /*
     * Coloca o nó em andamento.
     */

    $stmt = $pdo->prepare("
        UPDATE nos
        SET status = 'em_andamento'
        WHERE id = :id
    ");


    $stmt->execute([
        ':id' => $no_id
    ]);


    echo json_encode([

        'sucesso' => true,

        'mensagem' =>
            'Atendimento assumido com sucesso!',

        'pedido_id' =>
            $pdo->lastInsertId()

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        'sucesso' => false,

        'erro' =>
            'Erro ao criar atendimento.',

        'detalhes' =>
            $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
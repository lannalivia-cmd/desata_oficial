<?php

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


    $usuario_id = filter_input(
        INPUT_POST,
        'usuario_id',
        FILTER_VALIDATE_INT
    );

    $area_id = filter_input(
        INPUT_POST,
        'area_id',
        FILTER_VALIDATE_INT
    );

    $titulo = trim($_POST['titulo'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = strtoupper(trim($_POST['estado'] ?? ''));
    $publico_alvo = trim($_POST['publico_alvo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');


    if (
        !$usuario_id ||
        !$area_id ||
        $titulo === '' ||
        $cidade === '' ||
        $estado === '' ||
        $descricao === ''
    ) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Preencha todos os campos obrigatórios.'
        ]);

        exit;
    }


    if (strlen($estado) !== 2) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'O estado deve possuir 2 letras. Exemplo: SP.'
        ]);

        exit;
    }


    // Verifica usuário
    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id = :usuario_id
        LIMIT 1
    ");

    $stmt->execute([
        ':usuario_id' => $usuario_id
    ]);

    if (!$stmt->fetch()) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Usuário não encontrado.'
        ]);

        exit;
    }


    // Verifica área
    $stmt = $pdo->prepare("
        SELECT id
        FROM areas
        WHERE id = :area_id
        LIMIT 1
    ");

    $stmt->execute([
        ':area_id' => $area_id
    ]);

    if (!$stmt->fetch()) {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Área pedagógica não encontrada.'
        ]);

        exit;
    }


    // Salva o nó
    $stmt = $pdo->prepare("
        INSERT INTO nos (
            usuario_id,
            area_id,
            titulo,
            cidade,
            estado,
            publico_alvo,
            descricao,
            status
        )
        VALUES (
            :usuario_id,
            :area_id,
            :titulo,
            :cidade,
            :estado,
            :publico_alvo,
            :descricao,
            'aberto'
        )
    ");

    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':area_id' => $area_id,
        ':titulo' => $titulo,
        ':cidade' => $cidade,
        ':estado' => $estado,
        ':publico_alvo' => $publico_alvo,
        ':descricao' => $descricao
    ]);


    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Nó cadastrado com sucesso!',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao salvar o nó.',
        'detalhes' => $e->getMessage()
    ]);
}
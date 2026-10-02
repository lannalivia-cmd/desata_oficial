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

    $no_id = filter_input(INPUT_POST, 'no_id', FILTER_VALIDATE_INT);
    $professor_id = filter_input(INPUT_POST, 'professor_id', FILTER_VALIDATE_INT);

    $titulo = trim($_POST['titulo'] ?? '');
    $objetivo = trim($_POST['objetivo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (!$no_id || !$professor_id || $titulo === '' || $objetivo === '') {
        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Preencha todos os campos obrigatórios.'
        ]);

        exit;
    }

    // Verifica se o nó existe
    $stmt = $pdo->prepare("
        SELECT id
        FROM nos
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $no_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Nó não encontrado.'
        ]);

        exit;
    }

    // Verifica se o professor existe
    $stmt = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE id = :id
          AND tipo = 'professor'
          AND ativo = 1
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $professor_id
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Professor não encontrado ou usuário não é professor.'
        ]);

        exit;
    }

    // Salva o plano
    $stmt = $pdo->prepare("
        INSERT INTO planos_acao
        (
            no_id,
            professor_id,
            titulo,
            objetivo,
            descricao
        )
        VALUES
        (
            :no_id,
            :professor_id,
            :titulo,
            :objetivo,
            :descricao
        )
    ");

    $stmt->execute([
        ':no_id' => $no_id,
        ':professor_id' => $professor_id,
        ':titulo' => $titulo,
        ':objetivo' => $objetivo,
        ':descricao' => $descricao
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Plano de ação salvo com sucesso!',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao salvar plano de ação.',
        'detalhes' => $e->getMessage()
    ]);
}
?>
<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

$no_id = filter_input(
    INPUT_GET,
    'no_id',
    FILTER_VALIDATE_INT
);

if (!$no_id) {

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Nó não informado.'
    ]);

    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            no_id,
            professor_id,
            titulo,
            objetivo,
            descricao,
            data_inicio,
            data_fim,
            status,
            criado_em,
            atualizado_em

        FROM planos_acao

        WHERE no_id = :no_id

        ORDER BY criado_em DESC

        LIMIT 1
    ");

    $stmt->execute([
        ':no_id' => $no_id
    ]);

    $plano = $stmt->fetch();

    if (!$plano) {

        echo json_encode(
            null,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }

    echo json_encode(
        $plano,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao buscar plano.',
        'detalhes' => $e->getMessage()
    ]);
}
?>
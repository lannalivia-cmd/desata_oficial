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

    $pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);

    if (!$pedido_id) {
        http_response_code(400);
        echo json_encode([
            'sucesso' => false,
            'erro' => 'ID do pedido não informado.'
        ]);
        exit;
    }

    // Verifica se o pedido existe
    $stmt = $pdo->prepare("
        SELECT id, no_id, status
        FROM pedidos_ajuda
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $pedido_id
    ]);

    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        http_response_code(404);
        echo json_encode([
            'sucesso' => false,
            'erro' => 'Pedido não encontrado.'
        ]);
        exit;
    }

    // Atualiza o pedido
    $stmt = $pdo->prepare("
        UPDATE pedidos_ajuda
        SET status = 'aceito',
            respondido_em = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $pedido_id
    ]);

    // Atualiza também o status do nó
    $stmt = $pdo->prepare("
        UPDATE nos
        SET status = 'em_andamento'
        WHERE id = :no_id
    ");

    $stmt->execute([
        ':no_id' => $pedido['no_id']
    ]);

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pedido assumido com sucesso!'
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao assumir pedido.',
        'detalhes' => $e->getMessage()
    ]);
}
?>
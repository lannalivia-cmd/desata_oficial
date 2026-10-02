<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

try {

    $sql = "
        SELECT
            n.id,
            n.usuario_id,
            n.area_id,
            n.titulo,
            n.cidade,
            n.estado,
            n.publico_alvo,
            n.descricao,
            n.status,
            n.criado_em,
            n.atualizado_em,
            a.nome AS area,
            u.nome AS usuario_nome

        FROM nos n

        INNER JOIN areas a
            ON a.id = n.area_id

        INNER JOIN usuarios u
            ON u.id = n.usuario_id

        WHERE n.status = 'aberto'

        ORDER BY n.criado_em DESC
    ";

    $stmt = $pdo->query($sql);

    $nos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(
        $nos,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao buscar os nós.',
        'detalhes' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
```php
<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

try {

    /*
    |--------------------------------------------------------------------------
    | BUSCAR UM PEDIDO ESPECÍFICO
    |--------------------------------------------------------------------------
    */

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {

        $id = (int) $_GET['id'];

        $sql = "
            SELECT
                p.id,
                p.no_id,
                p.professor_id,
                p.mensagem,
                p.status,
                p.criado_em,
                p.respondido_em,

                n.titulo,
                n.cidade,
                n.estado,
                n.publico_alvo,
                n.descricao,

                a.id AS area_id,
                a.nome AS area,

                usuario.nome AS usuario_nome,
                usuario.email AS usuario_email,

                professor.nome AS professor_nome,
                professor.email AS professor_email

            FROM pedidos_ajuda p

            INNER JOIN nos n
                ON n.id = p.no_id

            INNER JOIN areas a
                ON a.id = n.area_id

            INNER JOIN usuarios usuario
                ON usuario.id = n.usuario_id

            INNER JOIN usuarios professor
                ON professor.id = p.professor_id

            WHERE p.id = :id

            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $pedido = $stmt->fetch();

        if (!$pedido) {

            http_response_code(404);

            echo json_encode([
                'sucesso' => false,
                'erro' => 'Pedido não encontrado.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }

        echo json_encode(
            $pedido,
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR TODOS OS PEDIDOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            p.id,
            p.no_id,
            p.professor_id,
            p.mensagem,
            p.status,
            p.criado_em,
            p.respondido_em,

            n.titulo,
            n.cidade,
            n.estado,
            n.publico_alvo,
            n.descricao,

            a.id AS area_id,
            a.nome AS area,

            usuario.nome AS usuario_nome,

            professor.nome AS professor_nome

        FROM pedidos_ajuda p

        INNER JOIN nos n
            ON n.id = p.no_id

        INNER JOIN areas a
            ON a.id = n.area_id

        INNER JOIN usuarios usuario
            ON usuario.id = n.usuario_id

        INNER JOIN usuarios professor
            ON professor.id = p.professor_id

        ORDER BY p.criado_em DESC
    ";

    $stmt = $pdo->query($sql);

    $pedidos = $stmt->fetchAll();

    echo json_encode(
        $pedidos,
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao buscar pedidos.',
        'detalhes' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

?>
```

```php
<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Método inválido. Use POST.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$no_id = isset($_POST['no_id']) ? (int) $_POST['no_id'] : 0;
$professor_id = isset($_POST['professor_id']) ? (int) $_POST['professor_id'] : 0;
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO
|--------------------------------------------------------------------------
*/

if ($no_id <= 0) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'ID do nó não informado.'
    ]);
    exit;
}

if ($professor_id <= 0) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'ID do professor não informado.'
    ]);
    exit;
}

if ($mensagem === '') {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Digite uma mensagem antes de enviar.'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| SALVAR NO BANCO
|--------------------------------------------------------------------------
*/

try {

    $sql = "
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
            'pendente'
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':no_id', $no_id, PDO::PARAM_INT);
    $stmt->bindValue(':professor_id', $professor_id, PDO::PARAM_INT);
    $stmt->bindValue(':mensagem', $mensagem, PDO::PARAM_STR);

    $stmt->execute();

    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Pedido de ajuda salvo com sucesso!',
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro ao salvar no banco de dados.',
        'detalhes' => $e->getMessage()
    ]);
}
```

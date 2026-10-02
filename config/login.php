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
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $email = trim($_POST['email'] ?? '');

    $senha = $_POST['senha'] ?? '';


    if ($email === '' || $senha === '') {

        http_response_code(400);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Preencha o e-mail e a senha.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $sql = "
        SELECT
            id,
            nome,
            email,
            senha,
            tipo,
            ativo
        FROM usuarios
        WHERE email = :email
        LIMIT 1
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':email' => $email
    ]);


    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$usuario) {

        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'E-mail ou senha incorretos.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if (!$usuario['ativo']) {

        http_response_code(403);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'Esta conta está desativada.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    if (!password_verify($senha, $usuario['senha'])) {

        http_response_code(401);

        echo json_encode([
            'sucesso' => false,
            'erro' => 'E-mail ou senha incorretos.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    // Criar sessão

    $_SESSION['usuario_id'] = $usuario['id'];

    $_SESSION['usuario_nome'] = $usuario['nome'];

    $_SESSION['usuario_email'] = $usuario['email'];

    $_SESSION['usuario_tipo'] = $usuario['tipo'];


    echo json_encode([

        'sucesso' => true,

        'mensagem' => 'Login realizado com sucesso!',

        'usuario' => [

            'id' => $usuario['id'],

            'nome' => $usuario['nome'],

            'email' => $usuario['email'],

            'tipo' => $usuario['tipo']

        ]

    ], JSON_UNESCAPED_UNICODE);


} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([

        'sucesso' => false,

        'erro' => 'Erro interno ao realizar login.',

        'detalhes' => $e->getMessage()

    ], JSON_UNESCAPED_UNICODE);

}
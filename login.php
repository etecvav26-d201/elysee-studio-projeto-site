<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {

        $erro = 'Preencha o e-mail e a senha.';

    } else {

        // Primeiro procura o usuário entre os clientes.
        $consulta = $pdo->prepare(
            'SELECT id, nome, email, senha
             FROM clientes
             WHERE email = :email
             LIMIT 1'
        );

        $consulta->execute([
            'email' => $email
        ]);

        $cliente = $consulta->fetch();

        if ($cliente && password_verify($senha, $cliente['senha'])) {

            $_SESSION['usuario_id'] = $cliente['id'];
            $_SESSION['usuario_nome'] = $cliente['nome'];
            $_SESSION['usuario_tipo'] = 'cliente';

            header('Location: index.php');
            exit;

        } else {

            // Caso não seja cliente, procura entre os funcionários.
            $consulta = $pdo->prepare(
                'SELECT id, nome, email, senha, cargo
                 FROM funcionarios
                 WHERE email = :email
                 AND ativo = 1
                 LIMIT 1'
            );

            $consulta->execute([
                'email' => $email
            ]);

            $funcionario = $consulta->fetch();

            if ($funcionario && password_verify($senha, $funcionario['senha'])) {

                $_SESSION['usuario_id'] = $funcionario['id'];
                $_SESSION['usuario_nome'] = $funcionario['nome'];
                $_SESSION['usuario_tipo'] = 'funcionario';
                $_SESSION['usuario_cargo'] = $funcionario['cargo'];

                // Registra o último login do funcionário.
                $atualizar = $pdo->prepare(
                    'UPDATE funcionarios
                     SET ultimo_login = NOW()
                     WHERE id = :id'
                );

                $atualizar->execute([
                    'id' => $funcionario['id']
                ]);

                header('Location: admin/painel.php');
                exit;

            } else {

                $erro = 'E-mail ou senha incorretos.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Elysee Studio</title>
</head>

<body>

    <main>

        <h1>Entrar</h1>

        <?php if ($erro !== ''): ?>

            <p><?= htmlspecialchars($erro) ?></p>

        <?php endif; ?>

        <form method="POST">

            <label for="email">E-mail</label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <label for="senha">Senha</label>

            <input
                type="password"
                id="senha"
                name="senha"
                required
            >

            <button type="submit">
                Entrar
            </button>

        </form>

        <p>
            Ainda não possui uma conta?
            <a href="cadastro.php">Criar conta</a>
        </p>

    </main>

</body>

</html>
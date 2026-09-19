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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="assets/js/navbar.js" defer></script>
    <link rel="icon" type="image/png" href="assets/img/icon.png">
</head>

<body>

    <body class="auth-page">

        <?php
        $navbarFile = __DIR__ . '/includes/navbar.php';

        if (is_file($navbarFile)) {
            require $navbarFile;
        }
        ?>

        <main class="auth-main">

            <div class="auth-container">

                <header class="auth-header">

                    <p class="eyebrow">
                        Elysee Studio
                    </p>

                    <h1 class="auth-title">
                        Bem-vindo <em>de volta.</em>
                    </h1>

                    <p class="auth-subtitle">
                        Entre na sua conta para continuar sua experiência no Elysee Studio.
                    </p>

                </header>

                <div class="auth-card">

                    <?php if ($erro !== ''): ?>

                        <p class="auth-message auth-error">
                            <?= htmlspecialchars($erro) ?>
                        </p>

                    <?php endif; ?>

                    <form method="POST" class="auth-form">

                        <div class="auth-field">

                            <label for="email">
                                E-mail
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="seuemail@email.com"
                                autocomplete="email"
                                required
                            >

                        </div>

                        <div class="auth-field">

                            <label for="senha">
                                Senha
                            </label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                        <button type="submit" class="auth-button">
                            Entrar
                        </button>

                    </form>

                    <p class="auth-footer">
                        Ainda não possui uma conta?
                        <a href="cadastro.php">Criar conta</a>
                    </p>

                </div>

            </div>

        </main>

        <?php
        $footerFile = __DIR__ . '/includes/footer.php';

        if (is_file($footerFile)) {
            require $footerFile;
        }
        ?>

    </body>

</body>

</html>
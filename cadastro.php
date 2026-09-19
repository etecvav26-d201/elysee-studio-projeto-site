<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($nome === '' || $email === '' || $telefone === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'As senhas não coincidem.';
    } else {

        $consulta = $pdo->prepare(
            'SELECT id FROM clientes WHERE email = :email LIMIT 1'
        );

        $consulta->execute([
            'email' => $email
        ]);

        if ($consulta->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $cadastro = $pdo->prepare(
                'INSERT INTO clientes (nome, email, telefone, senha)
                 VALUES (:nome, :email, :telefone, :senha)'
            );

            $cadastro->execute([
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'senha' => $senhaHash
            ]);

            $sucesso = 'Cadastro realizado com sucesso.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | Elysee Studio</title>
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
                        Crie sua <em>conta.</em>
                    </h1>

                    <p class="auth-subtitle">
                        Faça seu cadastro para ter acesso à sua experiência no Elysee Studio.
                    </p>

                </header>

                <div class="auth-card">

                    <?php if ($erro !== ''): ?>

                        <p class="auth-message auth-error">
                            <?= htmlspecialchars($erro) ?>
                        </p>

                    <?php endif; ?>

                    <?php if ($sucesso !== ''): ?>

                        <p class="auth-message auth-success">
                            <?= htmlspecialchars($sucesso) ?>
                        </p>

                    <?php endif; ?>

                    <form method="POST" class="auth-form">

                        <div class="auth-field">

                            <label for="nome">
                                Nome
                            </label>

                            <input
                                type="text"
                                id="nome"
                                name="nome"
                                placeholder="Seu nome completo"
                                autocomplete="name"
                                required
                            >

                        </div>

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

                            <label for="telefone">
                                Telefone
                            </label>

                            <input
                                type="tel"
                                id="telefone"
                                name="telefone"
                                placeholder="(00) 00000-0000"
                                autocomplete="tel"
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
                                placeholder="Mínimo de 6 caracteres"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <div class="auth-field">

                            <label for="confirmar_senha">
                                Confirmar senha
                            </label>

                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                placeholder="Digite sua senha novamente"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                        <button type="submit" class="auth-button">
                            Criar conta
                        </button>

                    </form>

                    <p class="auth-footer">
                        Já possui uma conta?
                        <a href="login.php">Entrar</a>
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
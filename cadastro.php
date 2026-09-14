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
</head>

<body>

    <main>

        <h1>Criar conta</h1>

        <?php if ($erro !== ''): ?>
            <p><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <?php if ($sucesso !== ''): ?>
            <p><?= htmlspecialchars($sucesso) ?></p>
        <?php endif; ?>

        <form method="POST">

            <label for="nome">Nome</label>
            <input
                type="text"
                id="nome"
                name="nome"
                required
            >

            <label for="email">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <label for="telefone">Telefone</label>
            <input
                type="tel"
                id="telefone"
                name="telefone"
                required
            >

            <label for="senha">Senha</label>
            <input
                type="password"
                id="senha"
                name="senha"
                required
            >

            <label for="confirmar_senha">Confirmar senha</label>
            <input
                type="password"
                id="confirmar_senha"
                name="confirmar_senha"
                required
            >

            <button type="submit">Criar conta</button>

        </form>

    </main>

</body>

</html>
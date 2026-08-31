<?php

// Obriga o PHP a verificar os tipos declarados de forma rigorosa.
declare(strict_types=1);

// Importa o arquivo da conexão com o banco.
require_once __DIR__ . '/includes/conexao.php';

// Armazena a mensagem que será apresentada ao usuário
$mensagem = '';

// Define o tipo da mensagem:
// 'erro' ou 'sucesso'.
$tipoMensagem = '';

// Guarda os dados preenchidos pelo usuário.
$dados = [
    'nome' => '',
    'telefone' => '',
    'email' => '',
    'data' => '',
    'hora' => '',
    'atendimento' => '',
    'observacoes' => '',
];

// Verifica se o formulário foi enviado utilizando POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera os valores enviados pelo formulário.
    $dados['nome'] = trim((string) ($_POST['nome'] ?? ''));
    $dados['telefone'] = trim((string) ($_POST['telefone'] ?? ''));
    $dados['email'] = trim((string) ($_POST['email'] ?? ''));
    $dados['data'] = trim((string) ($_POST['data'] ?? ''));
    $dados['hora'] = trim((string) ($_POST['hora'] ?? ''));
    $dados['atendimento'] = trim((string) ($_POST['atendimento'] ?? ''));
    $dados['observacoes'] = trim((string) ($_POST['observacoes'] ?? ''));

    // Verifica se algum dos campos obrigatórios está vazio.
    if (
        $dados['nome'] === '' ||
        $dados['telefone'] === '' ||
        $dados['email'] === '' ||
        $dados['data'] === '' ||
        $dados['hora'] === '' ||
        $dados['atendimento'] === ''
    ) {

        // Define a mensagem que será apresentada ao usuário.
        $mensagem = 'Preencha todos os campos obrigatórios.';
        // Define que a mensagem é um erro.
        $tipoMensagem = 'erro';

    // Verifica se o valor informado possui formato de e-mail válido.
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {

        $mensagem = 'Digite um e-mail válido.';
        $tipoMensagem = 'erro';

    } else {

        // Tenta transformar a data enviada pelo formulário em um objeto DateTime
        $data = DateTime::createFromFormat(
            'Y-m-d',
            $dados['data']
        );

        // Verifica se a data realmente é válida e se está no formato esperado.
        if (
            !$data ||
            $data->format('Y-m-d') !== $dados['data']
        ) {

            $mensagem = 'A data selecionada é inválida.';
            $tipoMensagem = 'erro';

        // Impede que o usuário faça um agendamento para uma data anterior ao dia atual.
        } elseif ($dados['data'] < date('Y-m-d')) {

            $mensagem = 'Não é possível agendar para uma data anterior a hoje.';
            $tipoMensagem = 'erro';

        } else {

            // try permite capturar possíveis erros gerados durante as operações com o banco.
            try {

                // Consulta se já existe um agendamento para a mesma data e horário.
                $stmt = $pdo->prepare(
                    'SELECT id
                     FROM agendamentos
                     WHERE data_agendamento = :data
                     AND hora_agendamento = :hora
                     AND status <> "cancelado"
                     LIMIT 1'
                );

                // Envia os valores para os parâmetros da consulta.
                $stmt->execute([
                    ':data' => $dados['data'],
                    ':hora' => $dados['hora'],
                ]);

                // Tenta encontrar um registro.
                $horarioOcupado = $stmt->fetch();

                // Se encontrou um agendamento, o horário está ocupado.
                if ($horarioOcupado) {

                    $mensagem = 'Esse horário já está reservado. Escolha outro horário.';
                    $tipoMensagem = 'erro';

                } else {

                    // Procura um cliente utilizando o e-mail.
                    $stmt = $pdo->prepare(
                        'SELECT id
                         FROM clientes
                         WHERE email = :email
                         LIMIT 1'
                    );

                    $stmt->execute([
                        ':email' => $dados['email'],
                    ]);

                    $cliente = $stmt->fetch();

                    if ($cliente) {

                        // O cliente já existe.
                        $clienteId = (int) $cliente['id'];

                        // Atualiza nome e telefone do cliente.
                        $stmt = $pdo->prepare(
                            'UPDATE clientes
                             SET nome = :nome,
                                 telefone = :telefone
                             WHERE id = :id'
                        );

                        $stmt->execute([
                            ':nome' => $dados['nome'],
                            ':telefone' => $dados['telefone'],
                            ':id' => $clienteId,
                        ]);

                    } else {

                        // O cliente ainda não existe, logo criando na tabela
                        $stmt = $pdo->prepare(
                            'INSERT INTO clientes
                            (
                                nome,
                                telefone,
                                email
                            )
                            VALUES
                            (
                                :nome,
                                :telefone,
                                :email
                            )'
                        );

                        $stmt->execute([
                            ':nome' => $dados['nome'],
                            ':telefone' => $dados['telefone'],
                            ':email' => $dados['email'],
                        ]);

                        $clienteId = (int) $pdo->lastInsertId();
                    }

                    // Insere o novo agendamento no banco.
                    $stmt = $pdo->prepare(
                        'INSERT INTO agendamentos
                        (
                            cliente_id,
                            data_agendamento,
                            hora_agendamento,
                            atendimento,
                            observacoes,
                            status
                        )
                        VALUES
                        (
                            :cliente_id,
                            :data,
                            :hora,
                            :atendimento,
                            :observacoes,
                            "pendente"
                        )'
                    );

                    // Envia os dados para a consulta preparada.
                    $stmt->execute([
                        ':cliente_id' => $clienteId,
                        ':data' => $dados['data'],
                        ':hora' => $dados['hora'],
                        ':atendimento' => $dados['atendimento'],
                        ':observacoes' => $dados['observacoes'],
                    ]);


                    $mensagem = 'Seu pedido de agendamento foi enviado com sucesso! Aguarde a confirmação do Elysee Studio.';
                    $tipoMensagem = 'sucesso';

                    $dados = [
                        'nome' => '',
                        'telefone' => '',
                        'email' => '',
                        'data' => '',
                        'hora' => '',
                        'atendimento' => '',
                        'observacoes' => '',
                    ];
                }

            } catch (Throwable $e) {

                // Registra o erro técnico no log do servidor.
                error_log(
                    'Elysee Studio — erro no agendamento: ' .
                    $e->getMessage()
                );

                $mensagem = 'Não foi possível realizar o agendamento agora. Tente novamente.';
                $tipoMensagem = 'erro';
            }
        }
    }
}

// htmlspecialchars transforma caracteres especiais em entidades HTML, isso evita que dados enviados pelo usuário sejam
// interpretados como HTML ou JavaScript.
function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Agende seu momento de cuidado no Elysee Studio.">
    <meta name="theme-color" content="#f5f0e8">
    <title>Agendar — Elysee Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/agendar.css">
</head>

<body>

    <?php
        $navbarFile = __DIR__ . '/includes/navbar.php';

        if (is_file($navbarFile)) {
            require $navbarFile;
        }
    ?>
    <main>

        <!--parte hero-->
        <section class="booking-hero">
            <div class="booking-hero-inner">
                <span class="eyebrow">
                    Elysee Studio
                </span>
                <h1>
                    Reserve um momento
                    <em>para você.</em>
                </h1>
                <p>
                    Escolha o dia e o horário que melhor combinam
                    com a sua rotina.
                </p>
            </div>
        </section>

        <!--formulario-->
        <section class="booking-section">
            <div class="booking-container">
                <div class="booking-heading">
                    <span class="section-number">
                        01
                    </span>
                    <div>
                        <span class="eyebrow">
                            Agendamento
                        </span>
                        <h2>
                            Vamos começar.
                        </h2>
                    </div>
                </div>

                <?php if ($mensagem !== ''): ?>
                    <div
                        class="form-message <?= e($tipoMensagem) ?>"
                    >
                        <?= e($mensagem) ?>

                    </div>
                <?php endif; ?>

                <form
                    action=""
                    method="POST"
                    class="booking-form"
                    id="bookingForm"
                >

                    <!--dados do cliente-->
                    <div class="form-section">
                        <div class="form-section-title">
                            <span>
                                01
                            </span>
                            <h3>
                                Seus dados
                            </h3>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="nome">
                                    Nome completo *
                                </label>
                                <input
                                    type="text"
                                    id="nome"
                                    name="nome"
                                    value="<?= e($dados['nome']) ?>"
                                    placeholder="Seu nome"
                                    autocomplete="name"
                                    required
                                >
                            </div>

                            <div class="field">
                                <label for="telefone">
                                    Telefone *
                                </label>
                                <input
                                    type="tel"
                                    id="telefone"
                                    name="telefone"
                                    value="<?= e($dados['telefone']) ?>"
                                    placeholder="(00) 00000-0000"
                                    autocomplete="tel"
                                    required
                                >
                            </div>

                            <div class="field field-full">

                                <label for="email">
                                    E-mail *
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= e($dados['email']) ?>"
                                    placeholder="seuemail@email.com"
                                    autocomplete="email"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <!--agendamento-->
                    <div class="form-section">
                        <div class="form-section-title">
                            <span>
                                02
                            </span>
                            <h3>
                                Seu momento
                            </h3>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="data">
                                    Data *
                                </label>
                                <input
                                    type="date"
                                    id="data"
                                    name="data"
                                    value="<?= e($dados['data']) ?>"
                                    min="<?= date('Y-m-d') ?>"
                                    required
                                >
                            </div>

                            <div class="field">
                                <label for="hora">
                                    Horário *
                                </label>
                                <select
                                    id="hora"
                                    name="hora"
                                    required
                                >
                                    <option value="">
                                        Escolha um horário
                                    </option>

                                    <option value="09:00">
                                        09:00
                                    </option>

                                    <option value="10:00">
                                        10:00
                                    </option>

                                    <option value="11:00">
                                        11:00
                                    </option>

                                    <option value="12:00">
                                        12:00
                                    </option>

                                    <option value="13:00">
                                        13:00
                                    </option>

                                    <option value="14:00">
                                        14:00
                                    </option>

                                    <option value="15:00">
                                        15:00
                                    </option>

                                    <option value="16:00">
                                        16:00
                                    </option>

                                    <option value="17:00">
                                        17:00
                                    </option>

                                    <option value="18:00">
                                        18:00
                                    </option>
                                </select>
                            </div>

                            <div class="field field-full">
                                <label for="atendimento">
                                    Atendimento *
                                </label>
                                <select
                                    id="atendimento"
                                    name="atendimento"
                                    required
                                >
                                    <option value="">
                                        Escolha o atendimento
                                    </option>

                                    <option value="Manicure Signature">
                                        Manicure Signature
                                    </option>

                                    <option value="Hair Ritual">
                                        Hair Ritual
                                    </option>

                                    <option value="Glow Facial">
                                        Glow Facial
                                    </option>

                                </select>
                            </div>
                        </div>
                    </div>

                    <!--observacoes-->
                    <div class="form-section">
                        <div class="form-section-title">
                            <span>
                                03
                            </span>
                            <h3>
                                Alguma observação?
                            </h3>
                        </div>

                        <div class="field">
                            <label for="observacoes">
                                Mensagem
                            </label>
                            <textarea
                                id="observacoes"
                                name="observacoes"
                                rows="5"
                                placeholder="Conte algo que gostaria que soubéssemos antes do seu atendimento."
                            ><?= e($dados['observacoes']) ?></textarea>
                        </div>
                    </div>

                    <!--envio-->
                    <div class="form-submit">

                        <p>
                            Ao enviar, seu horário ficará
                            <strong>pendente de confirmação.</strong>
                        </p>
                        <button
                            type="submit"
                            class="btn-submit"
                        >
                            <span>
                                Solicitar agendamento
                            </span>

                            <span>
                                ↗
                            </span>

                        </button>
                    </div>
                </form>
            </div>
        </section>

    </main>



    <?php
        $footerFile = __DIR__ . '/includes/footer.php';

        if (is_file($footerFile)) {
            require $footerFile;
        }
    ?>

    <script
        src="assets/js/agendar.js"
        defer
    ></script>

</body>
</html>

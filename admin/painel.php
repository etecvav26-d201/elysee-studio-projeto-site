<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/conexao.php';

$hoje = date('Y-m-d');

$dataSelecionada = $_GET['data'] ?? $hoje;

$dateObject = DateTime::createFromFormat('Y-m-d', $dataSelecionada);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $dataSelecionada
) {
    $dataSelecionada = $hoje;
}

$agendamentos = [];
$erroBanco = false;

$total = 0;
$pendentes = 0;
$confirmados = 0;
$concluidos = 0;
$cancelados = 0;

try {

    $stmt = $pdo->prepare(
        'SELECT
            a.id,
            a.data_agendamento,
            a.hora_agendamento,
            a.atendimento,
            a.observacoes,
            a.status,

            c.nome AS cliente,
            c.telefone,
            c.email,

            f.nome AS funcionario

        FROM agendamentos AS a

        INNER JOIN clientes AS c
            ON c.id = a.cliente_id

        LEFT JOIN funcionarios AS f
            ON f.id = a.funcionario_id

        WHERE a.data_agendamento = :data

        ORDER BY
            a.hora_agendamento ASC,
            a.id ASC'
    );

    $stmt->execute([
        ':data' => $dataSelecionada
    ]);

    $agendamentos = $stmt->fetchAll();

    $total = count($agendamentos);

    foreach ($agendamentos as $agendamento) {

        switch ($agendamento['status']) {

            case 'pendente':
                $pendentes++;
                break;

            case 'confirmado':
                $confirmados++;
                break;

            case 'concluido':
                $concluidos++;
                break;

            case 'cancelado':
                $cancelados++;
                break;
        }
    }

} catch (Throwable $e) {

    error_log(
        'Elysee Studio — erro no painel: ' .
        $e->getMessage()
    );

    $erroBanco = true;
}

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function formatarData(string $data): string
{
    $date = DateTime::createFromFormat(
        'Y-m-d',
        $data
    );

    return $date
        ? $date->format('d/m/Y')
        : $data;
}

function statusLabel(string $status): string
{
    return match ($status) {

        'pendente' => 'Pendente',

        'confirmado' => 'Confirmado',

        'concluido' => 'Concluído',

        'cancelado' => 'Cancelado',

        default => ucfirst($status)
    };
}

function statusClass(string $status): string
{
    return match ($status) {

        'pendente' => 'status-pendente',

        'confirmado' => 'status-confirmado',

        'concluido' => 'status-concluido',

        'cancelado' => 'status-cancelado',

        default => ''
    };
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Painel interno de agendamentos do Elysee Studio.">
    <title>Agendamentos — Elysee Studio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/painel-funcionarios.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
</head>


<body>

    <?php
    $navbarFile = __DIR__ . '/../includes/navbar.php';

    if (is_file($navbarFile)) {
        require $navbarFile;
    }
    ?>

    <main class="dashboard">

        <section class="dashboard-intro">

            <div>

                <h1>
                    Agendamentos dos clientes
                </h1>

            </div>


            <form
                method="GET"
                class="date-form"
            >

                <label for="data">
                    Data da agenda
                </label>

                <div class="date-control">

                    <input
                        type="date"
                        id="data"
                        name="data"
                        value="<?= e($dataSelecionada) ?>"
                        onchange="this.form.submit()"
                    >

                </div>

            </form>

        </section>

        <!-- caso de erro-->
        <?php if ($erroBanco): ?>

            <div class="database-alert">

                Não foi possível carregar os agendamentos.

                Verifique a conexão com o banco de dados.

            </div>

        <?php endif; ?>

        <section class="stats">
            <article class="stat-card stat-main">

                <span>
                    Total
                </span>

                <strong>
                    <?= $total ?>
                </strong>

                <small>
                    <?= e(formatarData($dataSelecionada)) ?>
                </small>
            </article>

            <article class="stat-card">

                <span>
                    Pendentes
                </span>

                <strong>
                    <?= $pendentes ?>
                </strong>

            </article>

            <article class="stat-card">

                <span>
                    Confirmados
                </span>

                <strong>
                    <?= $confirmados ?>
                </strong>

            </article>

            <article class="stat-card">

                <span>
                    Concluídos
                </span>

                <strong>
                    <?= $concluidos ?>
                </strong>

            </article>

            <article class="stat-card">

                <span>
                    Cancelados
                </span>

                <strong>
                    <?= $cancelados ?>
                </strong>

            </article>
        </section>

        <!--agendamentos-->
        <section class="appointments">

            <div class="section-heading">
                <div>
                    <span class="eyebrow">
                        Agenda do dia
                    </span>
                    <h2>
                        Atendimentos
                    </h2>
                </div>

                <span class="appointment-count">

                    <?= $total ?>

                    <?= $total === 1
                        ? 'agendamento'
                        : 'agendamentos'
                    ?>
                </span>
            </div>

            <?php if (!$agendamentos): ?>
                <div class="empty-state">
                    <h3>
                        Nenhum agendamento
                    </h3>
                    <p>

                        Não existem atendimentos cadastrados para

                        <?= e(
                            formatarData($dataSelecionada)
                        ) ?>.
                    </p>
                </div>

            <?php else: ?>
                <div class="appointment-list">
                    <?php foreach ($agendamentos as $agendamento): ?>
                        <article class="appointment-card">

                            <!-- HORÁRIO -->

                            <div class="appointment-time">

                                <strong>

                                    <?= e(
                                        substr(
                                            (string) $agendamento['hora_agendamento'],
                                            0,
                                            5
                                        )
                                    ) ?>

                                </strong>

                                <span>

                                    <?= e(
                                        formatarData(
                                            (string) $agendamento['data_agendamento']
                                        )
                                    ) ?>

                                </span>

                            </div>

                            <div class="appointment-client">

                                <span class="appointment-label">
                                    Cliente
                                </span>

                                <h3>

                                    <?= e(
                                        (string) $agendamento['cliente']
                                    ) ?>

                                </h3>


                                <div class="client-contact">


                                    <?php if (
                                        !empty($agendamento['telefone'])
                                    ): ?>

                                        <a
                                            href="tel:<?= e(
                                                (string) $agendamento['telefone']
                                            ) ?>"
                                        >

                                            <?= e(
                                                (string) $agendamento['telefone']
                                            ) ?>

                                        </a>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($agendamento['email'])
                                    ): ?>

                                        <a
                                            href="mailto:<?= e(
                                                (string) $agendamento['email']
                                            ) ?>"
                                        >

                                            <?= e(
                                                (string) $agendamento['email']
                                            ) ?>

                                        </a>

                                    <?php endif; ?>


                                </div>

                            </div>


                            <div class="appointment-service">

                                <span class="appointment-label">
                                    Atendimento
                                </span>

                                <strong>

                                    <?= e(
                                        (string) $agendamento['atendimento']
                                    ) ?>

                                </strong>


                                <?php if (
                                    !empty($agendamento['funcionario'])
                                ): ?>

                                    <span class="responsible">

                                        Com

                                        <?= e(
                                            (string) $agendamento['funcionario']
                                        ) ?>

                                    </span>

                                <?php endif; ?>


                            </div>

                            <!--status -->
                            <div class="appointment-status">

                                <span
                                    class="status <?= e(
                                        statusClass(
                                            (string) $agendamento['status']
                                        )
                                    ) ?>"
                                >

                                    <?= e(
                                        statusLabel(
                                            (string) $agendamento['status']
                                        )
                                    ) ?>

                                </span>


                                <?php if (
                                    !empty($agendamento['observacoes'])
                                ): ?>

                                    <button
                                        type="button"
                                        class="notes-toggle"
                                        data-notes="<?= e(
                                            (string) $agendamento['observacoes']
                                        ) ?>"
                                    >

                                        Observações

                                    </button>

                                <?php endif; ?>


                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


    </main>



    <?php
        $footerFile = __DIR__ . '/../includes/footer.php';

        if (is_file($footerFile)) {
            require $footerFile;
        }
    ?>

    <script
        src="../assets/js/painel-funcionarios.js"
        defer
    ></script>

</body>
</html>

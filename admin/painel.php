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

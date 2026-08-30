<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/conexao.php';

$mensagem = '';
$tipoMensagem = '';

$dados = [
    'nome' => '',
    'telefone' => '',
    'email' => '',
    'data' => '',
    'hora' => '',
    'atendimento' => '',
    'observacoes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dados['nome'] = trim((string) ($_POST['nome'] ?? ''));
    $dados['telefone'] = trim((string) ($_POST['telefone'] ?? ''));
    $dados['email'] = trim((string) ($_POST['email'] ?? ''));
    $dados['data'] = trim((string) ($_POST['data'] ?? ''));
    $dados['hora'] = trim((string) ($_POST['hora'] ?? ''));
    $dados['atendimento'] = trim((string) ($_POST['atendimento'] ?? ''));
    $dados['observacoes'] = trim((string) ($_POST['observacoes'] ?? ''));

    if (
        $dados['nome'] === '' ||
        $dados['telefone'] === '' ||
        $dados['email'] === '' ||
        $dados['data'] === '' ||
        $dados['hora'] === '' ||
        $dados['atendimento'] === ''
    ) {

        $mensagem = 'Preencha todos os campos obrigatórios.';
        $tipoMensagem = 'erro';

    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {

        $mensagem = 'Digite um e-mail válido.';
        $tipoMensagem = 'erro';

    } else {

        $data = DateTime::createFromFormat(
            'Y-m-d',
            $dados['data']
        );

        if (
            !$data ||
            $data->format('Y-m-d') !== $dados['data']
        ) {

            $mensagem = 'A data selecionada é inválida.';
            $tipoMensagem = 'erro';

        } elseif ($dados['data'] < date('Y-m-d')) {

            $mensagem = 'Não é possível agendar para uma data anterior a hoje.';
            $tipoMensagem = 'erro';

        } else {

            try {

                $stmt = $pdo->prepare(
                    'SELECT id
                     FROM agendamentos
                     WHERE data_agendamento = :data
                     AND hora_agendamento = :hora
                     AND status <> "cancelado"
                     LIMIT 1'
                );

                $stmt->execute([
                    ':data' => $dados['data'],
                    ':hora' => $dados['hora'],
                ]);

                $horarioOcupado = $stmt->fetch();


                if ($horarioOcupado) {

                    $mensagem = 'Esse horário já está reservado. Escolha outro horário.';
                    $tipoMensagem = 'erro';

                } else {

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

                        $clienteId = (int) $cliente['id'];

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

function e(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

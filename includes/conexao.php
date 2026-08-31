<?php

declare(strict_types=1);

// Endereço onde o servidor MySQL está sendo executado.
const DB_HOST = 'localhost';

// Nome do banco de dados utilizado pelo projeto.
const DB_NAME = 'elysee_studio';

// Usuário utilizado para acessar o banco.
const DB_USER = 'root';

// Senha do usuário do banco, vazia no momento
const DB_PASS = '';

try {
    // Cria uma conexão com o banco utilizando PDO de forma segura
    $pdo = new PDO(
        // Monta a string de conexão
        'mysql:host=' . DB_HOST . //servidor
        ';dbname=' . DB_NAME . //banco
        ';charset=utf8mb4', //codificação utilizada do teclado

        // usuario do banco
        DB_USER,

        // senha do banco
        DB_PASS,

        [
            // Faz o PDO lançar exceções quando ocorrer algum erro durante uma operação no banco.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // Faz os resultados das consultas serem retornados como arrays associativos.
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Desativa a emulação de prepared statements, isso permite utilizar as consultas 
            // preparadas nativas do banco, aumentando a segurança contra SQL Injection.
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    // Registra o erro real no log do servidor.
    error_log(
        'Elysee Studio — erro de conexão: ' .
        $e->getMessage()
    );

    // Informa ao navegador que ocorreu um erro interno do servidor.
    http_response_code(500);

    // mensagem pro usuario quando ocorre o erro.
    exit(
        'Não foi possível conectar ao sistema. ' .
        'Tente novamente mais tarde.'
    );
}
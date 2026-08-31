<?php

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_NAME = 'elysee_studio';
const DB_USER = 'root';
const DB_PASS = '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST .
        ';dbname=' . DB_NAME .
        ';charset=utf8mb4',

        DB_USER,
        DB_PASS,

        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    error_log(
        'Elysee Studio — erro de conexão: ' .
        $e->getMessage()
    );

    http_response_code(500);

    exit(
        'Não foi possível conectar ao sistema. ' .
        'Tente novamente mais tarde.'
    );
}
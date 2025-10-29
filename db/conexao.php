<?php

$db_host = 'localhost';
$db_name = 'sispresidio';
$db_user = '******';
$db_pass = '******';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
}

?>
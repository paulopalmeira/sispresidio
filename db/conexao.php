<?php

$db_host = 'sql105.infinityfree.com';
$db_name = 'if0_40269905_sispresidio';
$db_user = 'if0_40269905';
$db_pass = 'S10jPQunL6kF54K';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
}

?>
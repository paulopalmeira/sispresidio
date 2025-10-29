<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['tipo']) || !isset($_SESSION['nome'])) {
    session_destroy();
    header("Location: ../public/login.php");
    exit();
}

if (isset($required_role)) {
    if ($_SESSION['tipo'] !== $required_role) {
        header("Location: /index.php?error=acesso_negado");
        exit();
    }
}

?>

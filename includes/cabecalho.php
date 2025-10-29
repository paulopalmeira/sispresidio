<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
// Aluno: Paulo Roberto Palmeira - RGM: 29651301
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sispresidio</title>
    <link rel="stylesheet" href="https:
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <a class="navbar-brand" href="../public/index.php">Sispresidio</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?php if (isset($_SESSION["tipo"])): ?>
                <?php if ($_SESSION["tipo"] == 'Diretor'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../diretor/pavilhoes.php">Gerenciar Pavilhões</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../diretor/relatorio_movimentacoes.php">Relatório de Movimentações</a>
                    </li>
                <?php elseif ($_SESSION["tipo"] == 'Agente'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../agente/presos.php">Gerenciar Presos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../agente/movimentar_preso.php">Movimentar Preso</a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav">
            <?php if (isset($_SESSION["nome"])): ?>
                <li class="nav-item">
                    <span class="navbar-text">
                        Bem-vindo, <?php echo htmlspecialchars($_SESSION["nome"]); ?> (<?php echo htmlspecialchars($_SESSION["tipo"]); ?>)
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../public/logout.php">Sair</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="container-fluid pt-3"></div>
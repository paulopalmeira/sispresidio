<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
// Aluno: Paulo Roberto Palmeira - RGM: 29651301
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../db/conexao.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = trim($_POST['matricula'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($matricula) || empty($senha)) {
        $error_message = "Por favor, preencha a matrícula e a senha.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario, nome, matricula_funcional, senha, tipo FROM usuarios WHERE matricula_funcional = :matricula");
            $stmt->execute([':matricula' => $matricula]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                if (password_verify($senha, $usuario['senha'])) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nome'] = $usuario['nome'];
                    $_SESSION['tipo'] = $usuario['tipo'];
                    
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Matrícula ou senha inválida.";
                }
            } else {
                $error_message = "Matrícula ou senha inválida.";
            }
        } catch (PDOException $e) {
            $error_message = "Erro no servidor. Tente novamente mais tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sispresidio</title>
    <link rel="stylesheet" href="https:
    <link rel="stylesheet" href="../css/estilo.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>

<div class="container login-container">
    <h2 class="text-center mb-4">Sispresidio - Login</h2>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="matricula">Matrícula Funcional</label>
            <input type="text" class="form-control" id="matricula" name="matricula" required>
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" class="form-control" id="senha" name="senha" required>
        </div>
        <button type="submit" class="btn btn-green btn-block">Entrar</button>
    </form>

    <div class="card mt-4">
        <div class="card-body text-secondary text-center small">
            <p class="card-title font-weight-bold">Ambiente de Teste</p>
            <p class="card-text mb-1"><strong>Diretor</strong> &rarr; Matrícula: <code>diretor123</code> | Senha: <code>senha_diretor</code></p>
            <p class="card-text"><strong>Agente</strong> &rarr; Matrícula: <code>agente123</code> | Senha: <code>senha_agente</code></p>
        </div>
    </div>

    <p class="mt-4 text-center text-muted small">
        Atividade de Entrega da matéria <strong>PIT - Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma_001 - 2025</strong>
    </p>
</div>

</body>
</html>
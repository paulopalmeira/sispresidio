<?php
// Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Inclui o arquivo de conexão com o banco de dados
require_once __DIR__ . '/../db/conexao.php';

$error_message = '';

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = trim($_POST['matricula'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($matricula) || empty($senha)) {
        $error_message = "Por favor, preencha a matrícula e a senha.";
    } else {
        try {
            // 1. Busca o usuário pela matrícula
            $stmt = $pdo->prepare("SELECT id_usuario, nome, matricula_funcional, senha, tipo FROM usuarios WHERE matricula_funcional = :matricula");
            $stmt->execute([':matricula' => $matricula]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // 2. Verifica a senha
                // NOTA: No script de criação do banco, as senhas foram hasheadas.
                // As senhas são 'senha_diretor' para 'diretor123' e 'senha_agente' para 'agente123'.
                if (password_verify($senha, $usuario['senha'])) {
                    // 3. Login bem-sucedido: Salva dados na sessão
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nome'] = $usuario['nome'];
                    $_SESSION['tipo'] = $usuario['tipo'];
                    
                    // 4. Redireciona para o dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Matrícula ou senha inválida.";
                }
            } else {
                $error_message = "Matrícula ou senha inválida.";
            }
        } catch (PDOException $e) {
            // Log do erro (em um ambiente real)
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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- CSS Personalizado -->
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

    <p class="mt-3 text-center text-muted">
        Para teste: Diretor (matrícula: diretor123, senha: senha_diretor) | Agente (matrícula: agente123, senha: senha_agente)
    </p>
</div>

</body>
</html>

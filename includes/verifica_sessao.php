<?php
/**
 * Script para iniciar a sessão e verificar a autenticação do usuário.
 * Deve ser incluído no topo de todas as páginas restritas.
 * 
 * Parâmetros opcionais:
 * $required_role: String com o tipo de usuário necessário ('Diretor' ou 'Agente'). 
 * Se não for passado, apenas verifica se há um usuário logado.
 */

// Inicia a sessão se ainda não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado (se as variáveis de sessão existem)
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['tipo']) || !isset($_SESSION['nome'])) {
    // Se não estiver logado, destrói a sessão e redireciona para a página de login
    session_destroy();
    // Corrigido para apontar para o login dentro da pasta public
    header("Location: ../public/login.php"); // <--- CORREÇÃO
    exit();
}

// Verifica se um papel específico é necessário (controle de acesso)
if (isset($required_role)) {
    // Se o tipo de usuário logado não for o papel requerido
    if ($_SESSION['tipo'] !== $required_role) {
        // Redireciona para o dashboard com uma mensagem de erro (ou para uma página de "Acesso Negado")
        header("Location: /index.php?error=acesso_negado");
        exit();
    }
}

// Se chegou até aqui, o usuário está logado e tem o papel adequado.
// As variáveis de sessão $_SESSION['id_usuario'], $_SESSION['nome'], $_SESSION['tipo'] estão disponíveis.
?>

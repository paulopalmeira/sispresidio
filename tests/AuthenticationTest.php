<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
// Aluno: Paulo Roberto Palmeira - RGM: 29651301
use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('DB_NAME'));
            $this->pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->fail("Não foi possível conectar ao banco de dados de teste: " . $e->getMessage());
        }
    }

    public function testLoginDiretorComCredenciaisValidas()
    {
        $matricula = 'diretor123';
        $senha_correta = 'senha_diretor';
        $stmt = $this->pdo->prepare("SELECT senha FROM usuarios WHERE matricula_funcional = :matricula");
        $stmt->execute([':matricula' => $matricula]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($usuario, "Usuário 'diretor123' não encontrado no banco de dados.");
        $this->assertTrue(
            password_verify($senha_correta, $usuario['senha']),
            "A verificação de senha para 'diretor123' falhou."
        );
    }

    public function testLoginComCredenciaisInvalidas()
    {
        $matricula = 'agente123';
        $senha_incorreta = 'senha_errada_123';
        $stmt = $this->pdo->prepare("SELECT senha FROM usuarios WHERE matricula_funcional = :matricula");
        $stmt->execute([':matricula' => $matricula]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($usuario, "Usuário 'agente123' não encontrado para o teste de falha.");
        $this->assertFalse(
            password_verify($senha_incorreta, $usuario['senha']),
            "A verificação de senha incorreta deveria falhar, mas passou."
        );
    }

    public function testLoginComUsuarioInexistente()
    {
        $matricula = 'usuario_fantasma_999';
        $stmt = $this->pdo->prepare("SELECT senha FROM usuarios WHERE matricula_funcional = :matricula");
        $stmt->execute([':matricula' => $matricula]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertFalse($usuario, "A busca por um usuário inexistente deveria retornar falso.");
    }
}
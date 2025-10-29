<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
// Aluno: Paulo Roberto Palmeira - RGM: 29651301
use PHPUnit\Framework\TestCase;

class CadastroPresoTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST'), getenv('DB_NAME'));
            $this->pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->fail("Não foi possível conectar ao banco de dados de teste local: " . $e->getMessage());
        }
    }
    
    public function testCadastroDePresoComSucesso()
    {
        $this->pdo->beginTransaction();
        $matricula_teste = '999.999-9';
        $dados_preso = [':nome' => 'Preso de Teste Valido', ':matricula_preso' => $matricula_teste, ':data_entrada' => date('Y-m-d')];
        $sql = "INSERT INTO presos (nome, matricula_preso, data_entrada) VALUES (:nome, :matricula_preso, :data_entrada)";
        $stmt = $this->pdo->prepare($sql);
        $execucao_com_sucesso = $stmt->execute($dados_preso);
        $this->assertTrue($execucao_com_sucesso, "A execução do SQL para inserir um preso válido falhou.");
        $stmt_check = $this->pdo->prepare("SELECT nome FROM presos WHERE matricula_preso = :matricula");
        $stmt_check->execute([':matricula' => $matricula_teste]);
        $preso_inserido = $stmt_check->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($preso_inserido, "Não foi possível encontrar o preso que deveria ter sido cadastrado.");
        $this->assertEquals('Preso de Teste Valido', $preso_inserido['nome']);
        $this->pdo->rollBack();
    }

    public function testFalhaAoCadastrarPresoComMatriculaDuplicada()
    {
        $this->pdo->beginTransaction();
        $stmt_existente = $this->pdo->query("SELECT matricula_preso FROM presos LIMIT 1");
        $matricula_existente = $stmt_existente->fetchColumn();
        $this->assertNotFalse($matricula_existente, "Não foi possível encontrar um preso existente para o teste de duplicidade.");
        $this->expectException(PDOException::class);
        $this->expectExceptionCode(23000);
        $dados_preso_duplicado = [':nome' => 'Preso Duplicado', ':matricula_preso' => $matricula_existente, ':data_entrada' => date('Y-m-d')];
        $sql = "INSERT INTO presos (nome, matricula_preso, data_entrada) VALUES (:nome, :matricula_preso, :data_entrada)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($dados_preso_duplicado);
        $this->pdo->rollBack();
    }
}
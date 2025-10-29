<?php
use PHPUnit\Framework\TestCase;

class MovimentacaoTest extends TestCase
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

    public function testMovimentacaoBemSucedidaDePreso()
    {
        $this->pdo->beginTransaction();
        $stmt_agente = $this->pdo->query("SELECT id_usuario FROM usuarios WHERE matricula_funcional = 'agente123'");
        $agente = $stmt_agente->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($agente, "Agente 'agente123' não encontrado para realizar a movimentação.");
        $id_agente_responsavel = $agente['id_usuario'];
        $stmt_preso = $this->pdo->query("SELECT id_preso, id_cela_atual FROM presos WHERE id_cela_atual IS NOT NULL LIMIT 1");
        $preso_para_mover = $stmt_preso->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($preso_para_mover, "Nenhum preso encontrado em uma cela para iniciar o teste de movimentação.");
        $id_preso = $preso_para_mover['id_preso'];
        $id_cela_origem = $preso_para_mover['id_cela_atual'];
        $sql_cela_destino = "SELECT c.id_cela FROM celas c JOIN pavilhoes p ON c.id_pavilhao = p.id_pavilhao WHERE p.status = 'Ativo' AND c.id_cela != :id_cela_origem AND c.capacidade > (SELECT COUNT(*) FROM presos WHERE id_cela_atual = c.id_cela) LIMIT 1";
        $stmt_cela_destino = $this->pdo->prepare($sql_cela_destino);
        $stmt_cela_destino->execute([':id_cela_origem' => $id_cela_origem]);
        $cela_destino = $stmt_cela_destino->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($cela_destino, "Nenhuma cela de destino válida foi encontrada para completar o teste.");
        $id_cela_destino = $cela_destino['id_cela'];
        $motivo_teste = "Movimentação de teste automatizado";
        $sql_mov = "INSERT INTO movimentacoes (data_hora, motivo, id_preso, id_cela_origem, id_cela_destino, id_usuario_responsavel) VALUES (NOW(), :motivo, :id_preso, :id_cela_origem, :id_cela_destino, :id_usuario)";
        $stmt_mov = $this->pdo->prepare($sql_mov);
        $stmt_mov->execute([':motivo' => $motivo_teste, ':id_preso' => $id_preso, ':id_cela_origem' => $id_cela_origem, ':id_cela_destino' => $id_cela_destino, ':id_usuario' => $id_agente_responsavel]);
        $id_movimentacao_criada = $this->pdo->lastInsertId();
        $sql_update_preso = "UPDATE presos SET id_cela_atual = :id_cela_destino WHERE id_preso = :id_preso";
        $stmt_update_preso = $this->pdo->prepare($sql_update_preso);
        $stmt_update_preso->execute([':id_cela_destino' => $id_cela_destino, ':id_preso' => $id_preso]);
        $stmt_check_preso = $this->pdo->prepare("SELECT id_cela_atual FROM presos WHERE id_preso = :id_preso");
        $stmt_check_preso->execute([':id_preso' => $id_preso]);
        $cela_atual_do_preso = $stmt_check_preso->fetchColumn();
        $this->assertEquals($id_cela_destino, $cela_atual_do_preso, "O preso não foi atualizado para a cela de destino correta.");
        $stmt_check_mov = $this->pdo->prepare("SELECT * FROM movimentacoes WHERE id_movimentacao = :id_mov");
        $stmt_check_mov->execute([':id_mov' => $id_movimentacao_criada]);
        $registro_mov = $stmt_check_mov->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($registro_mov, "O registro da movimentação não foi encontrado no banco de dados.");
        $this->assertEquals($id_preso, $registro_mov['id_preso']);
        $this->assertEquals($id_cela_origem, $registro_mov['id_cela_origem']);
        $this->assertEquals($motivo_teste, $registro_mov['motivo']);
        $this->pdo->rollBack();
    }
}
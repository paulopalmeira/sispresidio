<?php
// Projeto Integrador Transdisciplinar em Sistemas de Informação II - Turma 001 - 2025
// Aluno: Paulo Roberto Palmeira - RGM: 29651301
require_once __DIR__ . '/conexao.php';

$primeiros_nomes = ['Carlos', 'João', 'Marcos', 'Fernando', 'Lucas', 'Ricardo', 'Sérgio', 'Diego', 'Bruno', 'Rafael', 'Alexandre', 'Felipe', 'Eduardo', 'Marcelo', 'Leandro', 'Thiago', 'Vinicius', 'Gustavo'];
$sobrenomes = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Almeida', 'Pereira', 'Lima', 'Gomes', 'Ribeiro', 'Martins', 'Carvalho', 'Costa', 'Mendes', 'Nunes', 'Barbosa', 'Araújo'];
$crimes = ['Tráfico de Drogas', 'Roubo Qualificado', 'Homicídio Simples', 'Furto', 'Estelionato', 'Associação Criminosa', 'Receptação', 'Porte Ilegal de Arma'];
$observacoes_parts = [
    'tatuagens' => [
        'Tatuagem de uma âncora no antebraço esquerdo.', 'Tatuagem tribal no pescoço.', 'Nome "Sofia" tatuado no peito.',
        'Tatuagem de uma carpa no braço direito.', 'Tatuagem de um escorpião na mão.', 'Costas com tatuagens de motivos orientais.',
        'Palhaço tatuado na panturrilha.', 'Cruz tatuada nas costas.', 'Teia de aranha no cotovelo.'
    ],
    'cicatrizes' => [
        'Cicatriz visível acima da sobrancelha direita.', 'Cicatriz de queimadura na mão esquerda.', 'Grande cicatriz de cirurgia no abdômen.',
        'Cicatriz de corte no lado esquerdo do rosto.', 'Falha na sobrancelha devido a uma cicatriz antiga.', 'Cicatriz de faca na bochecha.'
    ],
    'caracteristicas' => [
        'Manca levemente da perna direita.', 'Tem o hábito de estalar os dedos constantemente.', 'Evita contato visual prolongado.',
        'Calvície aparente.', 'Dente de ouro visível ao sorrir.', 'Fala de forma rápida e agitada.', 'Possui um tique nervoso de piscar o olho esquerdo.'
    ]
];

function gerar_valor_unico($funcao_geradora, &$array_usados) {
    do {
        $valor = call_user_func($funcao_geradora);
    } while (isset($array_usados[$valor]));
    $array_usados[$valor] = true;
    return $valor;
}

function gerar_cpf() {
    return sprintf('%03d.%03d.%03d-%02d', rand(100, 999), rand(100, 999), rand(100, 999), rand(1, 99));
}

function gerar_rg() {
    return sprintf('%02d.%03d.%03d-%s', rand(10, 50), rand(100, 999), rand(100, 999), rand(0, 1) ? rand(0, 9) : 'X');
}

function gerar_matricula_preso() {
    return sprintf('%03d.%03d-%d', rand(1, 999), rand(1, 999), rand(0, 9));
}

function gerar_data($inicio, $fim) {
    $timestamp = mt_rand(strtotime($inicio), strtotime($fim));
    return date('Y-m-d', $timestamp);
}

function gerar_observacao_criativa($parts) {
    $obs_final = [];
    $num_caracteristicas = rand(1, 2);
    $categorias = array_keys($parts);
    shuffle($categorias);
    for ($i = 0; $i < $num_caracteristicas; $i++) {
        $categoria_escolhida = $categorias[$i];
        $item_escolhido = $parts[$categoria_escolhida][array_rand($parts[$categoria_escolhida])];
        if (!in_array($item_escolhido, $obs_final)) {
            $obs_final[] = $item_escolhido;
        }
    }
    return implode(' ', $obs_final);
}

try {
    echo "Iniciando o povoamento do banco de dados...\n";

    $nomes_completos = [];
    foreach ($primeiros_nomes as $p_nome) {
        foreach ($sobrenomes as $s_nome) {
            $nomes_completos[] = "$p_nome $s_nome";
        }
    }
    shuffle($nomes_completos);

    $stmt_celas = $pdo->query("SELECT c.id_cela, c.capacidade, (SELECT COUNT(*) FROM presos WHERE id_cela_atual = c.id_cela) as ocupacao FROM celas c JOIN pavilhoes p ON c.id_pavilhao = p.id_pavilhao WHERE p.status = 'Ativo'");
    $celas_com_vagas = array_filter($stmt_celas->fetchAll(PDO::FETCH_ASSOC), fn($cela) => $cela['ocupacao'] < $cela['capacidade']);

    if (empty($celas_com_vagas)) {
        throw new Exception("Nenhuma cela com vagas foi encontrada. Abortando.");
    }

    $pdo->beginTransaction();

    $total_presos_a_criar = 20;
    if (count($nomes_completos) < $total_presos_a_criar) {
        throw new Exception("Não há combinações de nomes suficientes para criar {$total_presos_a_criar} presos únicos.");
    }

    $cpfs_usados = [];
    $rgs_usados = [];
    $matriculas_usadas = [];

    for ($i = 0; $i < $total_presos_a_criar; $i++) {
        $indice_cela = array_rand($celas_com_vagas);
        $id_cela_selecionada = $celas_com_vagas[$indice_cela]['id_cela'];

        $nome = array_pop($nomes_completos);
        $matricula = gerar_valor_unico('gerar_matricula_preso', $matriculas_usadas);
        $cpf = gerar_valor_unico('gerar_cpf', $cpfs_usados);
        $rg = gerar_valor_unico('gerar_rg', $rgs_usados);
        $data_nascimento = gerar_data('1970-01-01', '2002-12-31');
        $data_entrada = gerar_data('2020-01-01', date('Y-m-d'));
        $crime = $crimes[array_rand($crimes)];
        $observacoes = gerar_observacao_criativa($observacoes_parts);

        $sql = "INSERT INTO presos (nome, matricula_preso, cpf, rg, data_nascimento, sexo, data_entrada, crime_cometido, observacoes, id_cela_atual) VALUES (:nome, :matricula_preso, :cpf, :rg, :data_nascimento, 'MASCULINO', :data_entrada, :crime_cometido, :observacoes, :id_cela_atual)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome, ':matricula_preso' => $matricula, ':cpf' => $cpf, ':rg' => $rg,
            ':data_nascimento' => $data_nascimento, ':data_entrada' => $data_entrada,
            ':crime_cometido' => $crime, ':observacoes' => $observacoes, ':id_cela_atual' => $id_cela_selecionada
        ]);

        $celas_com_vagas[$indice_cela]['ocupacao']++;
        if ($celas_com_vagas[$indice_cela]['ocupacao'] >= $celas_com_vagas[$indice_cela]['capacidade']) {
            unset($celas_com_vagas[$indice_cela]);
            if (empty($celas_com_vagas)) {
                echo "Aviso: Todas as celas foram preenchidas. Parando na inserção de número " . ($i + 1) . ".\n";
                break;
            }
        }
        echo "Preso '{$nome}' cadastrado com sucesso.\n";
    }

    $pdo->commit();
    echo "\n-------------------------------------------------\n";
    echo "Povoamento concluído com sucesso! " . ($i + 1) . " presos únicos foram adicionados.\n";
    echo "-------------------------------------------------\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("ERRO: " . $e->getMessage() . "\n");
}
?>
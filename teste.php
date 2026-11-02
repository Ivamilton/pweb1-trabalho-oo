<?php

declare(strict_types=1);

// =====================================================================
// Roteiro de testes no terminal: php teste.php
// Mostre cada regra funcionando e cada recusa. Cole a saída em docs/testes.md.
// =====================================================================

require_once __DIR__ . '/src/Plano.php';
require_once __DIR__ . '/src/PlanoMensal.php';
require_once __DIR__ . '/src/PlanoTrimestral.php';
require_once __DIR__ . '/src/PlanoAnual.php';
require_once __DIR__ . '/src/Treino.php';
require_once __DIR__ . '/src/Aluno.php';
require_once __DIR__ . '/src/Academia.php';

echo "=== SISTEMA ACADEMIA — ROTEIRO DE TESTES ===\n\n";

// ---------------------------------------------------------------
// Teste 1: Criação dos planos (polimorfismo)
// ---------------------------------------------------------------
echo "=== Teste 1: Criação dos planos e polimorfismo ===\n";

$planoMensal     = new PlanoMensal('Plano Mensal', 89.90);
$planoTrimestral = new PlanoTrimestral('Plano Trimestral', 89.90);
$planoAnual      = new PlanoAnual('Plano Anual', 89.90);

$planosArray = [$planoMensal, $planoTrimestral, $planoAnual];

// OO3: Polimorfismo — foreach com objetos de classes diferentes
foreach ($planosArray as $plano) {
    echo "  {$plano->getTipo()} ({$plano->getNome()}): "
       . "R$ " . number_format($plano->calcularValorTotal(), 2, ',', '.')
       . " por {$plano->getDuracaoMeses()} mês(es)\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 2: Criação de aluno com dados válidos
// ---------------------------------------------------------------
echo "=== Teste 2: Criação de aluno com dados válidos ===\n";

$aluno1 = new Aluno('Maria da Silva', '123.456.789-01', $planoMensal);
echo "  Aluno criado: {$aluno1->getNome()}, CPF: {$aluno1->getCpfFormatado()}\n";
echo "  Plano: {$aluno1->getPlano()->getTipo()}\n\n";

// ---------------------------------------------------------------
// Teste 3: Regra de negócio — nome do aluno vazio
// ---------------------------------------------------------------
echo "=== Teste 3: Recusa — nome do aluno vazio ===\n";

try {
    $alunoInvalido = new Aluno('', '123.456.789-01', $planoMensal);
    echo "  ERRO: o nome vazio foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 4: Regra de negócio — CPF inválido
// ---------------------------------------------------------------
echo "=== Teste 4: Recusa — CPF inválido ===\n";

try {
    $alunoInvalido = new Aluno('João', '123', $planoMensal);
    echo "  ERRO: o CPF inválido foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 5: Regra de negócio — preço-base negativo
// ---------------------------------------------------------------
echo "=== Teste 5: Recusa — preço-base negativo ===\n";

try {
    $planoRuim = new PlanoMensal('Plano Grátis', -10.0);
    echo "  ERRO: o preço negativo foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 6: Composição — adicionar treinos ao aluno
// ---------------------------------------------------------------
echo "=== Teste 6: Composição — adicionando treinos ===\n";

$treino1 = new Treino('Treino A - Peito e Tríceps', 'segunda', 60);
$treino2 = new Treino('Treino B - Costas e Bíceps', 'quarta', 75);
$treino3 = new Treino('Treino C - Pernas', 'sexta', 90);

$aluno1->adicionarTreino($treino1);
$aluno1->adicionarTreino($treino2);
$aluno1->adicionarTreino($treino3);

echo "  Treinos de {$aluno1->getNome()}:\n";
foreach ($aluno1->getTreinos() as $t) {
    echo "    - {$t->getNome()} ({$t->getDiaSemana()}, {$t->getDuracaoMinutos()} min)\n";
}
echo "  Carga horária semanal: {$aluno1->calcularCargaHorariaSemanal()} minutos\n\n";

// ---------------------------------------------------------------
// Teste 7: Regra de negócio — treino duplicado no mesmo dia
// ---------------------------------------------------------------
echo "=== Teste 7: Recusa — treino duplicado no mesmo dia ===\n";

try {
    $treinoDuplicado = new Treino('Treino Extra', 'segunda', 30);
    $aluno1->adicionarTreino($treinoDuplicado);
    echo "  ERRO: o treino duplicado foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 8: Regra de negócio — dia da semana inválido
// ---------------------------------------------------------------
echo "=== Teste 8: Recusa — dia da semana inválido ===\n";

try {
    $treinoInvalido = new Treino('Treino X', 'feriado', 60);
    echo "  ERRO: o dia inválido foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 9: Regra de negócio — duração do treino fora do limite
// ---------------------------------------------------------------
echo "=== Teste 9: Recusa — duração do treino acima de 240 min ===\n";

try {
    $treinoLongo = new Treino('Treino Longo', 'sabado', 300);
    echo "  ERRO: a duração excessiva foi aceita\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 10: Academia — adicionar alunos e calcular receita
// ---------------------------------------------------------------
echo "=== Teste 10: Academia — alunos e receita mensal ===\n";

$academia = new Academia('FitBoa Viagem');

$aluno2 = new Aluno('Pedro Santos', '987.654.321-00', $planoTrimestral);
$aluno3 = new Aluno('Ana Oliveira', '111.222.333-44', $planoAnual);

$academia->adicionarAluno($aluno1);
$academia->adicionarAluno($aluno2);
$academia->adicionarAluno($aluno3);

echo "  Total de alunos: " . count($academia->getAlunos()) . "\n";
echo "  Receita mensal estimada: R$ " . number_format($academia->calcularReceitaMensal(), 2, ',', '.') . "\n\n";

// ---------------------------------------------------------------
// Teste 11: Regra de negócio — CPF duplicado na academia
// ---------------------------------------------------------------
echo "=== Teste 11: Recusa — CPF duplicado na academia ===\n";

try {
    $alunoDuplicado = new Aluno('Maria Clone', '123.456.789-01', $planoMensal);
    $academia->adicionarAluno($alunoDuplicado);
    echo "  ERRO: o CPF duplicado foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

// ---------------------------------------------------------------
// Teste 12: paraArray() — saída JSON
// ---------------------------------------------------------------
echo "=== Teste 12: paraArray() — saída JSON ===\n";

$json = json_encode($academia->paraArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo $json . "\n\n";

// ---------------------------------------------------------------
// Teste 13: Regra de negócio — máximo de 6 treinos
// ---------------------------------------------------------------
echo "=== Teste 13: Recusa — máximo de 6 treinos por aluno ===\n";

$aluno4 = new Aluno('Carlos Lima', '555.666.777-88', $planoMensal);
$aluno4->adicionarTreino(new Treino('Treino 1', 'segunda', 60));
$aluno4->adicionarTreino(new Treino('Treino 2', 'terca', 60));
$aluno4->adicionarTreino(new Treino('Treino 3', 'quarta', 60));
$aluno4->adicionarTreino(new Treino('Treino 4', 'quinta', 60));
$aluno4->adicionarTreino(new Treino('Treino 5', 'sexta', 60));
$aluno4->adicionarTreino(new Treino('Treino 6', 'sabado', 60));

try {
    $aluno4->adicionarTreino(new Treino('Treino 7', 'domingo', 60));
    echo "  ERRO: o 7º treino foi aceito\n";
} catch (Exception $e) {
    echo "  OK, recusado: {$e->getMessage()}\n";
}
echo "\n";

echo "=== TODOS OS TESTES CONCLUÍDOS ===\n";

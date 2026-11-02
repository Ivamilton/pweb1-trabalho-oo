<?php

declare(strict_types=1);

// =====================================================================
// Ponto de entrada do site. Toda a lógica no topo; o HTML depois.
// Execute na raiz do repositório:
//   php -S localhost:8000 -t public -d display_errors=1 -d output_buffering=0
// =====================================================================

// 1. Carregar as classes (uma por arquivo, na pasta src/)
require_once __DIR__ . '/../src/Plano.php';
require_once __DIR__ . '/../src/PlanoMensal.php';
require_once __DIR__ . '/../src/PlanoTrimestral.php';
require_once __DIR__ . '/../src/PlanoAnual.php';
require_once __DIR__ . '/../src/Treino.php';
require_once __DIR__ . '/../src/Aluno.php';
require_once __DIR__ . '/../src/Academia.php';

// 2. Criar os objetos (eles são criados de novo a cada requisição)
//    Bônus: carrega dados salvos em JSON, se existir.
$dataFile = __DIR__ . '/../data/academia.json';

$academia = new Academia('FitBoa Viagem');

// Planos disponíveis
$planos = [
    'mensal'     => new PlanoMensal('Plano Mensal', 89.90),
    'trimestral' => new PlanoTrimestral('Plano Trimestral', 89.90),
    'anual'      => new PlanoAnual('Plano Anual', 89.90),
];

// Bônus: restaurar dados persistidos
if (file_exists($dataFile)) {
    $dados = json_decode(file_get_contents($dataFile), true);
    if (is_array($dados)) {
        foreach ($dados as $d) {
            try {
                $tipoPlano = strtolower($d['plano'] ?? 'mensal');
                $plano = $planos[$tipoPlano] ?? $planos['mensal'];

                // Recria um plano novo para cada aluno
                switch ($tipoPlano) {
                    case 'trimestral':
                        $planoAluno = new PlanoTrimestral('Plano Trimestral', 89.90);
                        break;
                    case 'anual':
                        $planoAluno = new PlanoAnual('Plano Anual', 89.90);
                        break;
                    default:
                        $planoAluno = new PlanoMensal('Plano Mensal', 89.90);
                }

                $aluno = new Aluno($d['nome'], $d['cpf'], $planoAluno);

                if (isset($d['treinos']) && is_array($d['treinos'])) {
                    foreach ($d['treinos'] as $t) {
                        $treino = new Treino($t['nome'], $t['diaSemana'], (int) $t['duracaoMinutos']);
                        $aluno->adicionarTreino($treino);
                    }
                }

                $academia->adicionarAluno($aluno);
            } catch (Exception $e) {
                // Dados corrompidos: ignora o registro
            }
        }
    }
}

// 3. Tratar o formulário (POST), com try/catch
$mensagemSucesso = '';
$mensagemErro    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'cadastrar_aluno') {
        try {
            $nomeAluno = $_POST['nome'] ?? '';
            $cpfAluno  = $_POST['cpf'] ?? '';
            $tipoPlano = strtolower($_POST['plano'] ?? 'mensal');

            switch ($tipoPlano) {
                case 'trimestral':
                    $planoAluno = new PlanoTrimestral('Plano Trimestral', 89.90);
                    break;
                case 'anual':
                    $planoAluno = new PlanoAnual('Plano Anual', 89.90);
                    break;
                default:
                    $planoAluno = new PlanoMensal('Plano Mensal', 89.90);
            }

            $aluno = new Aluno($nomeAluno, $cpfAluno, $planoAluno);
            $academia->adicionarAluno($aluno);

            // Bônus: salvar no arquivo JSON
            $dadosSalvar = [];
            foreach ($academia->getAlunos() as $a) {
                $treinosSalvar = [];
                foreach ($a->getTreinos() as $t) {
                    $treinosSalvar[] = $t->paraArray();
                }
                $dadosSalvar[] = [
                    'nome'    => $a->getNome(),
                    'cpf'     => $a->getCpf(),
                    'plano'   => strtolower($a->getPlano()->getTipo()),
                    'treinos' => $treinosSalvar,
                ];
            }
            $dir = dirname($dataFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($dataFile, json_encode($dadosSalvar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $mensagemSucesso = "Aluno \"{$nomeAluno}\" cadastrado com sucesso!";
        } catch (Exception $e) {
            $mensagemErro = $e->getMessage();
        }
    } elseif ($acao === 'adicionar_treino') {
        try {
            $cpfAluno       = $_POST['cpf_aluno'] ?? '';
            $nomeTreino     = $_POST['nome_treino'] ?? '';
            $diaSemana      = $_POST['dia_semana'] ?? '';
            $duracaoMinutos = (int) ($_POST['duracao_minutos'] ?? 0);

            // Encontrar o aluno pelo CPF
            $alunoEncontrado = null;
            foreach ($academia->getAlunos() as $a) {
                if ($a->getCpf() === preg_replace('/\D/', '', $cpfAluno)) {
                    $alunoEncontrado = $a;
                    break;
                }
            }

            if ($alunoEncontrado === null) {
                throw new Exception('Aluno não encontrado com o CPF informado.');
            }

            $treino = new Treino($nomeTreino, $diaSemana, $duracaoMinutos);
            $alunoEncontrado->adicionarTreino($treino);

            // Bônus: salvar no arquivo JSON
            $dadosSalvar = [];
            foreach ($academia->getAlunos() as $a) {
                $treinosSalvar = [];
                foreach ($a->getTreinos() as $t) {
                    $treinosSalvar[] = $t->paraArray();
                }
                $dadosSalvar[] = [
                    'nome'    => $a->getNome(),
                    'cpf'     => $a->getCpf(),
                    'plano'   => strtolower($a->getPlano()->getTipo()),
                    'treinos' => $treinosSalvar,
                ];
            }
            $dir = dirname($dataFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($dataFile, json_encode($dadosSalvar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $mensagemSucesso = "Treino \"{$nomeTreino}\" adicionado com sucesso!";
        } catch (Exception $e) {
            $mensagemErro = $e->getMessage();
        }
    }
}

// 4. Rota JSON (?formato=json): header, json_encode com paraArray(), exit
if (isset($_GET['formato']) && $_GET['formato'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($academia->paraArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 5. Escolher a página (?pagina=...) e o status 404
$pagina = $_GET['pagina'] ?? 'inicio';
$paginasValidas = ['inicio', 'planos', 'cadastrar', 'treinos', 'alunos'];
$titulo = 'FitBoa Viagem';
$status = 200;

if (!in_array($pagina, $paginasValidas, true)) {
    $pagina = '404';
    $status = 404;
    http_response_code(404);
}

// OO3. Polimorfismo: foreach que percorre planos de classes diferentes
$resumoPlanos = [];
foreach ($planos as $plano) {
    $resumoPlanos[] = [
        'tipo'  => $plano->getTipo(),
        'nome'  => $plano->getNome(),
        'valor' => $plano->calcularValorTotal(),
        'meses' => $plano->getDuracaoMeses(),
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($titulo) ?> — Sistema Academia</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <!-- 6. Cabeçalho e menu -->
  <header>
    <h1>🏋️ <?= htmlspecialchars($academia->getNome()) ?></h1>
    <nav>
      <a href="?pagina=inicio">Início</a>
      <a href="?pagina=planos">Planos</a>
      <a href="?pagina=cadastrar">Cadastrar Aluno</a>
      <a href="?pagina=treinos">Adicionar Treino</a>
      <a href="?pagina=alunos">Alunos</a>
      <a href="?formato=json">JSON</a>
    </nav>
  </header>

  <main>
    <?php if ($mensagemSucesso): ?>
      <div class="mensagem sucesso"><?= htmlspecialchars($mensagemSucesso) ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
      <div class="mensagem erro"><?= htmlspecialchars($mensagemErro) ?></div>
    <?php endif; ?>

    <?php if ($pagina === 'inicio'): ?>
      <!-- Página Inicial -->
      <h2>Bem-vindo à <?= htmlspecialchars($academia->getNome()) ?>!</h2>
      <p>Sistema de gerenciamento de academia com planos mensal, trimestral e anual.</p>
      <div class="cards">
        <div class="card">
          <h3>👥 Alunos</h3>
          <p class="destaque"><?= count($academia->getAlunos()) ?></p>
        </div>
        <div class="card">
          <h3>💰 Receita Mensal</h3>
          <p class="destaque">R$ <?= number_format($academia->calcularReceitaMensal(), 2, ',', '.') ?></p>
        </div>
        <div class="card">
          <h3>📋 Planos</h3>
          <p class="destaque"><?= count($planos) ?></p>
        </div>
      </div>

    <?php elseif ($pagina === 'planos'): ?>
      <!-- Página Planos (OO3: polimorfismo) -->
      <h2>Planos Disponíveis</h2>
      <table>
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Nome</th>
            <th>Duração</th>
            <th>Valor Total</th>
            <th>Valor/Mês</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resumoPlanos as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['tipo']) ?></td>
              <td><?= htmlspecialchars($r['nome']) ?></td>
              <td><?= $r['meses'] ?> <?= $r['meses'] === 1 ? 'mês' : 'meses' ?></td>
              <td>R$ <?= number_format($r['valor'], 2, ',', '.') ?></td>
              <td>R$ <?= number_format($r['valor'] / $r['meses'], 2, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php elseif ($pagina === 'cadastrar'): ?>
      <!-- W3. Formulário POST -->
      <h2>Cadastrar Aluno</h2>
      <form method="POST" action="?pagina=cadastrar">
        <input type="hidden" name="acao" value="cadastrar_aluno">

        <label for="nome">Nome completo:</label>
        <input type="text" id="nome" name="nome" required placeholder="Ex: João da Silva">

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00">

        <label for="plano">Plano:</label>
        <select id="plano" name="plano">
          <option value="mensal">Mensal — R$ 89,90/mês</option>
          <option value="trimestral">Trimestral — R$ 242,73 (10% desc.)</option>
          <option value="anual">Anual — R$ 863,04 (20% desc.)</option>
        </select>

        <button type="submit">Cadastrar</button>
      </form>

    <?php elseif ($pagina === 'treinos'): ?>
      <!-- Formulário para adicionar treino a um aluno -->
      <h2>Adicionar Treino</h2>

      <?php if (count($academia->getAlunos()) === 0): ?>
        <p>Nenhum aluno cadastrado. <a href="?pagina=cadastrar">Cadastre um aluno</a> primeiro.</p>
      <?php else: ?>
        <form method="POST" action="?pagina=treinos">
          <input type="hidden" name="acao" value="adicionar_treino">

          <label for="cpf_aluno">Aluno (CPF):</label>
          <select id="cpf_aluno" name="cpf_aluno">
            <?php foreach ($academia->getAlunos() as $a): ?>
              <option value="<?= htmlspecialchars($a->getCpf()) ?>">
                <?= htmlspecialchars($a->getNome()) ?> — <?= htmlspecialchars($a->getCpfFormatado()) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="nome_treino">Nome do treino:</label>
          <input type="text" id="nome_treino" name="nome_treino" required placeholder="Ex: Treino A - Peito e Tríceps">

          <label for="dia_semana">Dia da semana:</label>
          <select id="dia_semana" name="dia_semana">
            <option value="segunda">Segunda</option>
            <option value="terca">Terça</option>
            <option value="quarta">Quarta</option>
            <option value="quinta">Quinta</option>
            <option value="sexta">Sexta</option>
            <option value="sabado">Sábado</option>
            <option value="domingo">Domingo</option>
          </select>

          <label for="duracao_minutos">Duração (minutos):</label>
          <input type="number" id="duracao_minutos" name="duracao_minutos" required min="1" max="240" value="60">

          <button type="submit">Adicionar Treino</button>
        </form>
      <?php endif; ?>

    <?php elseif ($pagina === 'alunos'): ?>
      <!-- Lista de alunos com treinos -->
      <h2>Alunos Cadastrados</h2>

      <?php if (count($academia->getAlunos()) === 0): ?>
        <p>Nenhum aluno cadastrado ainda. <a href="?pagina=cadastrar">Cadastre o primeiro!</a></p>
      <?php else: ?>
        <?php foreach ($academia->getAlunos() as $aluno): ?>
          <div class="aluno-card">
            <h3><?= htmlspecialchars($aluno->getNome()) ?></h3>
            <p><strong>CPF:</strong> <?= htmlspecialchars($aluno->getCpfFormatado()) ?></p>
            <p><strong>Plano:</strong> <?= htmlspecialchars($aluno->getPlano()->getTipo()) ?>
               — R$ <?= number_format($aluno->getPlano()->calcularValorTotal(), 2, ',', '.') ?></p>
            <p><strong>Carga horária semanal:</strong> <?= $aluno->calcularCargaHorariaSemanal() ?> min</p>

            <?php if (count($aluno->getTreinos()) > 0): ?>
              <table class="treinos-table">
                <thead>
                  <tr>
                    <th>Treino</th>
                    <th>Dia</th>
                    <th>Duração</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($aluno->getTreinos() as $treino): ?>
                    <tr>
                      <td><?= htmlspecialchars($treino->getNome()) ?></td>
                      <td><?= htmlspecialchars(ucfirst($treino->getDiaSemana())) ?></td>
                      <td><?= $treino->getDuracaoMinutos() ?> min</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php else: ?>
              <p><em>Sem treinos cadastrados.</em></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php elseif ($pagina === '404'): ?>
      <!-- Página não encontrada -->
      <h2>404 — Página não encontrada</h2>
      <p>A página que você procura não existe. <a href="?pagina=inicio">Voltar ao início</a>.</p>

    <?php endif; ?>
  </main>

  <!-- 8. Rodapé -->
  <footer>
    <p>&copy; 2026 <?= htmlspecialchars($academia->getNome()) ?> — Sistema Web OO em PHP | IFCE Campus Boa Viagem</p>
  </footer>
</body>
</html>

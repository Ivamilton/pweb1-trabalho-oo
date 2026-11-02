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
                // Registro corrompido é ignorado
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

            // Localizar aluno pelo CPF
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

            $mensagemSucesso = "Treino \"{$nomeTreino}\" vinculado com sucesso!";
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
  <title><?= htmlspecialchars($titulo) ?> — Sistema de Gestão Fitness</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <!-- 6. Cabeçalho e Menu Moderno -->
  <header>
    <div class="header-container">
      <a href="?pagina=inicio" class="brand">
        <div class="brand-icon">
          <!-- Ícone Dumbbell SVG -->
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.5 6.5h11M6 20v-2a6 6 0 0 1 12 0v2M4 8h2M18 8h2M4 16h2M18 16h2M2 10v4M22 10v4M6 6v12M18 6v12"/>
          </svg>
        </div>
        <div class="brand-text">
          <h1><?= htmlspecialchars($academia->getNome()) ?></h1>
          <span>Gestão Web Orientada a Objetos</span>
        </div>
      </a>

      <nav>
        <a href="?pagina=inicio" class="<?= $pagina === 'inicio' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Início
        </a>
        <a href="?pagina=planos" class="<?= $pagina === 'planos' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
          </svg>
          Planos
        </a>
        <a href="?pagina=cadastrar" class="<?= $pagina === 'cadastrar' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
          </svg>
          Cadastrar Aluno
        </a>
        <a href="?pagina=treinos" class="<?= $pagina === 'treinos' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/>
          </svg>
          Adicionar Treino
        </a>
        <a href="?pagina=alunos" class="<?= $pagina === 'alunos' ? 'active' : '' ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Alunos
        </a>
        <a href="?formato=json" class="nav-json" title="Visualizar API JSON">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
          </svg>
          JSON
        </a>
      </nav>
    </div>
  </header>

  <main>
    <!-- Notificações com Ícones Vetoriais -->
    <?php if ($mensagemSucesso): ?>
      <div class="mensagem sucesso">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span><?= htmlspecialchars($mensagemSucesso) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
      <div class="mensagem erro">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span><?= htmlspecialchars($mensagemErro) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($pagina === 'inicio'): ?>
      <!-- Dashboard Inicial -->
      <div class="page-header">
        <div>
          <h2>Painel Geral de Controle</h2>
          <p class="page-subtitle">Visão executiva das matrículas, contratos e receita do estabelecimento.</p>
        </div>
      </div>

      <div class="cards-grid">
        <div class="stat-card">
          <div class="stat-info">
            <span>Alunos Matriculados</span>
            <div class="stat-value"><?= count($academia->getAlunos()) ?></div>
            <div class="stat-sub">Cadastros ativos</div>
          </div>
          <div class="stat-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <span>Receita Mensal Estimada</span>
            <div class="stat-value">R$ <?= number_format($academia->calcularReceitaMensal(), 2, ',', '.') ?></div>
            <div class="stat-sub">Rateio proporcional por mês</div>
          </div>
          <div class="stat-icon icon-emerald">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <span>Planos Ofertados</span>
            <div class="stat-value"><?= count($planos) ?></div>
            <div class="stat-sub">Mensal, Trimestral e Anual</div>
          </div>
          <div class="stat-icon icon-amber">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="banner">
        <div class="banner-content">
          <h3>Administração Rápida de Atividades</h3>
          <p>Cadastre novos alunos ou prescreva rotinas de treinos semanais com salvaguardas fisiológicas integradas.</p>
        </div>
        <div class="banner-actions">
          <a href="?pagina=cadastrar" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Novo Aluno
          </a>
          <a href="?pagina=treinos" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            Adicionar Treino
          </a>
        </div>
      </div>

    <?php elseif ($pagina === 'planos'): ?>
      <!-- Página Planos (OO3: polimorfismo) -->
      <div class="page-header">
        <div>
          <h2>Tabela Comparativa de Planos</h2>
          <p class="page-subtitle">Demonstração de polimorfismo: cada plano calcula seu valor e desconto dinamicamente.</p>
        </div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Modalidade</th>
              <th>Denominação</th>
              <th>Vigência</th>
              <th>Benefício</th>
              <th>Valor Total</th>
              <th>Custo Efetivo / Mês</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resumoPlanos as $r): ?>
              <tr>
                <td>
                  <?php if ($r['tipo'] === 'Mensal'): ?>
                    <span class="badge badge-mensal">Mensal</span>
                  <?php elseif ($r['tipo'] === 'Trimestral'): ?>
                    <span class="badge badge-trimestral">Trimestral</span>
                  <?php else: ?>
                    <span class="badge badge-anual">Anual</span>
                  <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($r['nome']) ?></strong></td>
                <td><?= $r['meses'] ?> <?= $r['meses'] === 1 ? 'mês' : 'meses' ?></td>
                <td>
                  <?php if ($r['tipo'] === 'Trimestral'): ?>
                    <span class="badge badge-discount">10% de desconto</span>
                  <?php elseif ($r['tipo'] === 'Anual'): ?>
                    <span class="badge badge-discount">20% de desconto</span>
                  <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 0.8rem;">Taxa Padrão</span>
                  <?php endif; ?>
                </td>
                <td><strong>R$ <?= number_format($r['valor'], 2, ',', '.') ?></strong></td>
                <td style="color: var(--primary); font-weight: 700;">R$ <?= number_format($r['valor'] / $r['meses'], 2, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($pagina === 'cadastrar'): ?>
      <!-- W3. Formulário POST Cadastrar Aluno -->
      <div class="page-header">
        <div>
          <h2>Matrícula de Novo Aluno</h2>
          <p class="page-subtitle">Preencha os dados cadastrais e selecione o plano de adesão desejado.</p>
        </div>
      </div>

      <div class="form-card">
        <form method="POST" action="?pagina=cadastrar">
          <input type="hidden" name="acao" value="cadastrar_aluno">

          <div class="form-group">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required placeholder="Exemplo: João da Silva">
          </div>

          <div class="form-group">
            <label for="cpf">
              CPF (Cadastro de Pessoa Física):
              <span class="label-hint">(11 dígitos numéricos)</span>
            </label>
            <input type="text" id="cpf" name="cpf" required placeholder="000.000.000-00">
          </div>

          <div class="form-group">
            <label for="plano">Modalidade de Plano Contratado:</label>
            <select id="plano" name="plano">
              <option value="mensal">Plano Mensal — R$ 89,90 / mês (Vigência de 1 mês)</option>
              <option value="trimestral">Plano Trimestral — R$ 242,73 (10% de desconto aplicado)</option>
              <option value="anual">Plano Anual — R$ 863,04 (20% de desconto aplicado)</option>
            </select>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
              </svg>
              Concluir Cadastro
            </button>
          </div>
        </form>
      </div>

    <?php elseif ($pagina === 'treinos'): ?>
      <!-- Formulário POST Adicionar Treino -->
      <div class="page-header">
        <div>
          <h2>Vincular Sessão de Treino</h2>
          <p class="page-subtitle">Associe rotinas semanais respeitando o limite fisiológico de 6 treinos e dias não repetidos.</p>
        </div>
      </div>

      <?php if (count($academia->getAlunos()) === 0): ?>
        <div class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
            </svg>
          </div>
          <h3>Nenhum Aluno Cadastrado</h3>
          <p>Para agendar uma sessão de treino, cadastre primeiro pelo menos um aluno no sistema.</p>
          <a href="?pagina=cadastrar" class="btn btn-primary">Cadastrar Primeiro Aluno</a>
        </div>
      <?php else: ?>
        <div class="form-card">
          <form method="POST" action="?pagina=treinos">
            <input type="hidden" name="acao" value="adicionar_treino">

            <div class="form-group">
              <label for="cpf_aluno">Selecionar Aluno:</label>
              <select id="cpf_aluno" name="cpf_aluno">
                <?php foreach ($academia->getAlunos() as $a): ?>
                  <option value="<?= htmlspecialchars($a->getCpf()) ?>">
                    <?= htmlspecialchars($a->getNome()) ?> — CPF: <?= htmlspecialchars($a->getCpfFormatado()) ?> (<?= count($a->getTreinos()) ?> treinos)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="nome_treino">Descrição da Sessão / Grupamento:</label>
              <input type="text" id="nome_treino" name="nome_treino" required placeholder="Exemplo: Treino A - Peitoral e Tríceps">
            </div>

            <div class="form-group">
              <label for="dia_semana">Dia da Semana:</label>
              <select id="dia_semana" name="dia_semana">
                <option value="segunda">Segunda-feira</option>
                <option value="terca">Terça-feira</option>
                <option value="quarta">Quarta-feira</option>
                <option value="quinta">Quinta-feira</option>
                <option value="sexta">Sexta-feira</option>
                <option value="sabado">Sábado</option>
                <option value="domingo">Domingo</option>
              </select>
            </div>

            <div class="form-group">
              <label for="duracao_minutos">
                Duração Estimada:
                <span class="label-hint">(entre 1 e 240 minutos)</span>
              </label>
              <input type="number" id="duracao_minutos" name="duracao_minutos" required min="1" max="240" value="60">
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 5v14M5 12h14"/>
                </svg>
                Adicionar Treino
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>

    <?php elseif ($pagina === 'alunos'): ?>
      <!-- Listagem Detalhada de Alunos -->
      <div class="page-header">
        <div>
          <h2>Prontuário de Alunos Matriculados</h2>
          <p class="page-subtitle">Consulta de contratos vigentes, carga horária acumulada e divisão semanal de treinos.</p>
        </div>
        <div>
          <a href="?pagina=cadastrar" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Novo Cadastro
          </a>
        </div>
      </div>

      <?php if (count($academia->getAlunos()) === 0): ?>
        <div class="empty-state">
          <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
          </div>
          <h3>Nenhum Aluno na Base</h3>
          <p>A carteira de alunos está vazia no momento. Cadastre o primeiro aluno para começar.</p>
          <a href="?pagina=cadastrar" class="btn btn-primary">Cadastrar Aluno</a>
        </div>
      <?php else: ?>
        <?php foreach ($academia->getAlunos() as $aluno): ?>
          <?php
            $iniciais = '';
            $partesNome = explode(' ', trim($aluno->getNome()));
            if (!empty($partesNome[0])) $iniciais .= strtoupper(substr($partesNome[0], 0, 1));
            if (count($partesNome) > 1) $iniciais .= strtoupper(substr(end($partesNome), 0, 1));
          ?>
          <div class="aluno-card">
            <div class="aluno-card-header">
              <div class="aluno-profile">
                <div class="avatar-circle"><?= htmlspecialchars($iniciais ?: 'A') ?></div>
                <div class="aluno-info">
                  <h3><?= htmlspecialchars($aluno->getNome()) ?></h3>
                  <span>CPF: <?= htmlspecialchars($aluno->getCpfFormatado()) ?></span>
                </div>
              </div>
              <div>
                <?php $tPlano = $aluno->getPlano()->getTipo(); ?>
                <?php if ($tPlano === 'Mensal'): ?>
                  <span class="badge badge-mensal">Plano Mensal</span>
                <?php elseif ($tPlano === 'Trimestral'): ?>
                  <span class="badge badge-trimestral">Plano Trimestral</span>
                <?php else: ?>
                  <span class="badge badge-anual">Plano Anual</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="aluno-meta-grid">
              <div class="meta-item">
                <span>Valor do Contrato</span>
                <strong>R$ <?= number_format($aluno->getPlano()->calcularValorTotal(), 2, ',', '.') ?></strong>
              </div>
              <div class="meta-item">
                <span>Duração Contratual</span>
                <strong><?= $aluno->getPlano()->getDuracaoMeses() ?> <?= $aluno->getPlano()->getDuracaoMeses() === 1 ? 'mês' : 'meses' ?></strong>
              </div>
              <div class="meta-item">
                <span>Carga Horária Semanal</span>
                <strong style="color: var(--primary);"><?= $aluno->calcularCargaHorariaSemanal() ?> minutos</strong>
              </div>
              <div class="meta-item">
                <span>Total de Sessões</span>
                <strong><?= count($aluno->getTreinos()) ?> / 6 treinos</strong>
              </div>
            </div>

            <div class="treinos-section-title">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              Rotina Semanal de Exercícios
            </div>

            <?php if (count($aluno->getTreinos()) > 0): ?>
              <div class="table-container" style="margin-bottom: 0;">
                <table>
                  <thead>
                    <tr>
                      <th>Sessão / Grupamento</th>
                      <th>Dia da Semana</th>
                      <th>Duração Estimada</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($aluno->getTreinos() as $treino): ?>
                      <tr>
                        <td><strong><?= htmlspecialchars($treino->getNome()) ?></strong></td>
                        <td><span class="badge badge-day"><?= htmlspecialchars(ucfirst($treino->getDiaSemana())) ?></span></td>
                        <td><?= $treino->getDuracaoMinutos() ?> min</td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">
                Nenhum treino vinculado a este aluno ainda. <a href="?pagina=treinos" style="color: var(--primary); font-weight: 600;">Adicionar treino</a>
              </p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    <?php elseif ($pagina === '404'): ?>
      <!-- Página 404 Not Found -->
      <div class="error-404-container">
        <div class="error-badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          HTTP 404 — Not Found
        </div>
        <h2>Página Não Encontrada</h2>
        <p style="color: var(--text-muted); margin: 0.75rem auto 1.5rem; max-width: 450px;">
          O recurso solicitado na query string não existe ou foi temporariamente movido.
        </p>
        <a href="?pagina=inicio" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Retornar ao Início
        </a>
      </div>

    <?php endif; ?>
  </main>

  <!-- 8. Rodapé Institucional -->
  <footer>
    <p>
      &copy; 2026 <strong><?= htmlspecialchars($academia->getNome()) ?></strong> — Sistema Web Orientado a Objetos em PHP | IFCE Campus Boa Viagem
    </p>
  </footer>
</body>
</html>

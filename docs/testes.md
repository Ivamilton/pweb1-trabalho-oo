# Testes

## Roteiro de testes no terminal

Comando: `php teste.php`

```text
=== SISTEMA ACADEMIA — ROTEIRO DE TESTES ===

=== Teste 1: Criação dos planos e polimorfismo ===
  Mensal (Plano Mensal): R$ 89,90 por 1 mês(es)
  Trimestral (Plano Trimestral): R$ 242,73 por 3 mês(es)
  Anual (Plano Anual): R$ 863,04 por 12 mês(es)

=== Teste 2: Criação de aluno com dados válidos ===
  Aluno criado: Maria da Silva, CPF: 123.456.789-01
  Plano: Mensal

=== Teste 3: Recusa — nome do aluno vazio ===
  OK, recusado: O nome do aluno não pode ser vazio.

=== Teste 4: Recusa — CPF inválido ===
  OK, recusado: CPF inválido. Use o formato 000.000.000-00 (11 dígitos).

=== Teste 5: Recusa — preço-base negativo ===
  OK, recusado: O preço-base deve ser maior que zero.

=== Teste 6: Composição — adicionando treinos ===
  Treinos de Maria da Silva:
    - Treino A - Peito e Tríceps (segunda, 60 min)
    - Treino B - Costas e Bíceps (quarta, 75 min)
    - Treino C - Pernas (sexta, 90 min)
  Carga horária semanal: 225 minutos

=== Teste 7: Recusa — treino duplicado no mesmo dia ===
  OK, recusado: Já existe um treino na segunda para este aluno.

=== Teste 8: Recusa — dia da semana inválido ===
  OK, recusado: Dia da semana inválido: feriado. Use: segunda, terca, quarta, quinta, sexta, sabado, domingo.

=== Teste 9: Recusa — duração do treino acima de 240 min ===
  OK, recusado: A duração do treino deve ser entre 1 e 240 minutos.

=== Teste 10: Academia — alunos e receita mensal ===
  Total de alunos: 3
  Receita mensal estimada: R$ 242,73

=== Teste 11: Recusa — CPF duplicado na academia ===
  OK, recusado: Já existe um aluno cadastrado com este CPF.

=== Teste 12: paraArray() — saída JSON ===
{
    "nome": "FitBoa Viagem",
    "totalAlunos": 3,
    "receitaMensal": 242.73,
    "alunos": [
        {
            "nome": "Maria da Silva",
            "cpf": "123.456.789-01",
            "plano": {
                "tipo": "Mensal",
                "nome": "Plano Mensal",
                "precoBase": 89.9,
                "duracaoMeses": 1,
                "valorTotal": 89.9
            },
            "treinos": [
                {
                    "nome": "Treino A - Peito e Tríceps",
                    "diaSemana": "segunda",
                    "duracaoMinutos": 60
                },
                {
                    "nome": "Treino B - Costas e Bíceps",
                    "diaSemana": "quarta",
                    "duracaoMinutos": 75
                },
                {
                    "nome": "Treino C - Pernas",
                    "diaSemana": "sexta",
                    "duracaoMinutos": 90
                }
            ],
            "cargaHorariaSemanal": 225
        },
        {
            "nome": "Pedro Santos",
            "cpf": "987.654.321-00",
            "plano": {
                "tipo": "Trimestral",
                "nome": "Plano Trimestral",
                "precoBase": 89.9,
                "duracaoMeses": 3,
                "valorTotal": 242.73
            },
            "treinos": [],
            "cargaHorariaSemanal": 0
        },
        {
            "nome": "Ana Oliveira",
            "cpf": "111.222.333-44",
            "plano": {
                "tipo": "Anual",
                "nome": "Plano Anual",
                "precoBase": 89.9,
                "duracaoMeses": 12,
                "valorTotal": 863.04
            },
            "treinos": [],
            "cargaHorariaSemanal": 0
        }
    ]
}

=== Teste 13: Recusa — máximo de 6 treinos por aluno ===
  OK, recusado: Um aluno pode ter no máximo 6 treinos.

=== TODOS OS TESTES CONCLUÍDOS ===
```

**Explicação de cada linha:**

- **Teste 1** — Cria os três tipos de plano (`PlanoMensal`, `PlanoTrimestral`, `PlanoAnual`) herdados da classe abstrata `Plano`. Demonstra polimorfismo: um laço `foreach` itera sobre objetos de classes distintas e chama os mesmos métodos (`getTipo()`, `getNome()`, `calcularValorTotal()`, `getDuracaoMeses()`), obtendo comportamentos específicos de cada subclasse com base em suas taxas e descontos.
- **Teste 2** — Cria uma instância válida da classe `Aluno` associada a um plano. Comprova a inicialização correta de atributos privados, a formatação de CPF e os getters.
- **Teste 3** — Testa a validação do construtor de `Aluno` ao passar nome vazio (`""`). Comprova que a regra de negócio dispara `throw new Exception` com a mensagem `"O nome do aluno não pode ser vazio."`, capturada pelo bloco `catch`.
- **Teste 4** — Testa a validação de CPF curto (`"123"`). Comprova a recusa de CPF que não contenha 11 dígitos numéricos, disparando a exceção correspondente.
- **Teste 5** — Testa a validação do construtor de `Plano` ao informar preço-base menor ou igual a zero (`-10.0`). Comprova o lançamento da exceção `"O preço-base deve ser maior que zero."`.
- **Teste 6** — Demonstra a composição: três objetos da classe `Treino` são adicionados à lista interna do aluno através de `adicionarTreino()`. O método `calcularCargaHorariaSemanal()` soma a duração dos treinos cadastrados (60 + 75 + 90 = 225 minutos).
- **Teste 7** — Testa a regra que proíbe dois treinos no mesmo dia da semana para o mesmo aluno. Tenta cadastrar um segundo treino na `"segunda"`, sendo devidamente recusado com a mensagem `"Já existe um treino na segunda para este aluno."`.
- **Teste 8** — Testa a validação do construtor de `Treino` ao passar um dia inválido (`"feriado"`). Comprova a recusa informando os dias válidos da semana.
- **Teste 9** — Testa o limite de duração de um treino ao informar 300 minutos (limite é 240). A exceção é lançada e capturada.
- **Teste 10** — Demonstra a composição de `Academia` contendo uma lista de objetos `Aluno`. Adiciona 3 alunos e calcula a receita mensal estimada através de `calcularReceitaMensal()`, rateando planos trimestrais e anuais por mês.
- **Teste 11** — Testa a regra de unicidade de CPF na academia: tenta cadastrar um segundo aluno com o mesmo CPF da Maria da Silva. A inserção é recusada com `"Já existe um aluno cadastrado com este CPF."`.
- **Teste 12** — Testa a serialização para array associativo através de `paraArray()` em cascata (`Academia` -> `Aluno` -> `Plano` e `Treino`), gerando a representação completa em JSON com `json_encode`.
- **Teste 13** — Testa a regra de negócio do teto de treinos: após cadastrar 6 treinos (de segunda a sábado), tenta adicionar um 7º treino. A operação é recusada com `"Um aluno pode ter no máximo 6 treinos."`.

---

## Camada web

**Uma página que existe:** `curl.exe -i "http://localhost:8000/?pagina=planos"`

```text
HTTP/1.1 200 OK
Host: 127.0.0.1:8000
Date: Mon, 02 Nov 2026 18:11:18 GMT
Connection: close
X-Powered-By: PHP/8.2.33
Content-type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FitBoa Viagem — Sistema Academia</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <!-- 6. Cabeçalho e Menu Moderno -->
  <header>
    <div class="header-container">
      <a href="?pagina=inicio" class="brand">
        <div class="brand-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">...</svg>
        </div>
        <div class="brand-text">
          <h1>FitBoa Viagem</h1>
          <span>Gestão Web Orientada a Objetos</span>
        </div>
      </a>
      <nav>
        <a href="?pagina=inicio">Início</a>
        <a href="?pagina=planos" class="active">Planos</a>
        <a href="?pagina=cadastrar">Cadastrar Aluno</a>
        <a href="?pagina=treinos">Adicionar Treino</a>
        <a href="?pagina=alunos">Alunos</a>
        <a href="?formato=json" class="nav-json">JSON</a>
      </nav>
    </div>
  </header>

  <main>
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
        <tr>
          <td>Mensal</td>
          <td>Plano Mensal</td>
          <td>1 mês</td>
          <td>R$ 89,90</td>
          <td>R$ 89,90</td>
        </tr>
        <tr>
          <td>Trimestral</td>
          <td>Plano Trimestral</td>
          <td>3 meses</td>
          <td>R$ 242,73</td>
          <td>R$ 80,91</td>
        </tr>
        <tr>
          <td>Anual</td>
          <td>Plano Anual</td>
          <td>12 meses</td>
          <td>R$ 863,04</td>
          <td>R$ 71,92</td>
        </tr>
      </tbody>
    </table>
  </main>
```

**Uma página que não existe:** `curl.exe -i "http://localhost:8000/?pagina=inexistente"`

```text
HTTP/1.1 404 Not Found
Host: 127.0.0.1:8000
Date: Mon, 02 Nov 2026 18:11:19 GMT
Connection: close
X-Powered-By: PHP/8.2.33
Content-type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>FitBoa Viagem — Sistema Academia</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <!-- 6. Cabeçalho e Menu Moderno -->
  <header>
    <div class="header-container">
      <a href="?pagina=inicio" class="brand">
        <div class="brand-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">...</svg>
        </div>
        <div class="brand-text">
          <h1>FitBoa Viagem</h1>
          <span>Gestão Web Orientada a Objetos</span>
        </div>
      </a>
      <nav>
        <a href="?pagina=inicio">Início</a>
        <a href="?pagina=planos" class="active">Planos</a>
        <a href="?pagina=cadastrar">Cadastrar Aluno</a>
        <a href="?pagina=treinos">Adicionar Treino</a>
        <a href="?pagina=alunos">Alunos</a>
        <a href="?formato=json" class="nav-json">JSON</a>
      </nav>
    </div>
  </header>

  <main>
    <!-- Página não encontrada -->
    <h2>404 — Página não encontrada</h2>
    <p>A página que você procura não existe. <a href="?pagina=inicio">Voltar ao início</a>.</p>
  </main>
```

**A rota JSON:** `curl.exe -i "http://localhost:8000/?formato=json"`

```text
HTTP/1.1 200 OK
Host: 127.0.0.1:8000
Date: Mon, 02 Nov 2026 18:11:19 GMT
Connection: close
X-Powered-By: PHP/8.2.33
Content-Type: application/json; charset=utf-8

{
    "nome": "FitBoa Viagem",
    "totalAlunos": 1,
    "receitaMensal": 89.9,
    "alunos": [
        {
            "nome": "Carlos Silva",
            "cpf": "123.456.789-01",
            "plano": {
                "tipo": "Mensal",
                "nome": "Plano Mensal",
                "precoBase": 89.9,
                "duracaoMeses": 1,
                "valorTotal": 89.9
            },
            "treinos": [],
            "cargaHorariaSemanal": 0
        }
    ]
}
```

**O formulário com um dado inválido:**

Comando de envio com nome em branco via POST:
`curl.exe -i -X POST -d "acao=cadastrar_aluno&nome=&cpf=12345678901&plano=mensal" "http://localhost:8000/?pagina=cadastrar"`

Resposta retornada pelo servidor (exceção capturada com try/catch e renderizada com segurança):

```text
HTTP/1.1 200 OK
Host: 127.0.0.1:8000
Date: Mon, 02 Nov 2026 18:11:19 GMT
Connection: close
X-Powered-By: PHP/8.2.33
Content-type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
...
  <main>
    <div class="mensagem erro">O nome do aluno não pode ser vazio.</div>

    <!-- W3. Formulário POST -->
    <h2>Cadastrar Aluno</h2>
    <form method="POST" action="?pagina=cadastrar">
...
```

Log do servidor PHP Development Server:
```text
[Mon Nov  2 15:11:19 2026] 127.0.0.1:51280 [200]: POST /?pagina=cadastrar
```
A requisição HTTP POST foi processada com código 200, a exceção `"O nome do aluno não pode ser vazio."` foi capturada pelo bloco `try/catch` da camada web e exibida com destaque na tag `<div class="mensagem erro">`.

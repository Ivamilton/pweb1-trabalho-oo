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

- **Teste 1** — Cria os três tipos de plano (Mensal, Trimestral, Anual) e usa polimorfismo (`foreach`) para chamar `calcularValorTotal()` e `getTipo()` em cada um. Cada classe filha responde de forma diferente: o mensal retorna R$ 89,90, o trimestral aplica 10% de desconto (R$ 242,73), e o anual aplica 20% (R$ 863,04).
- **Teste 2** — Cria um aluno com dados válidos e verifica que o construtor funcionou corretamente.
- **Teste 3** — Tenta criar um aluno com nome vazio. A exceção é lançada e capturada, comprovando a regra de negócio.
- **Teste 4** — Tenta criar um aluno com CPF de apenas 3 dígitos. A validação recusa, comprovando que CPFs precisam ter 11 dígitos.
- **Teste 5** — Tenta criar um plano com preço negativo. A classe `Plano` recusa no construtor.
- **Teste 6** — Adiciona 3 treinos a um aluno (composição) e calcula a carga horária semanal total (225 min = 60+75+90).
- **Teste 7** — Tenta adicionar um treino na segunda-feira para um aluno que já tem treino nesse dia. A regra recusa a duplicata.
- **Teste 8** — Tenta criar um treino com dia "feriado", que não está na lista de dias válidos. A exceção informa os dias aceitos.
- **Teste 9** — Tenta criar um treino com duração de 300 minutos, acima do limite de 240. A validação recusa.
- **Teste 10** — Adiciona 3 alunos à academia e calcula a receita mensal estimada.
- **Teste 11** — Tenta adicionar um aluno com CPF já cadastrado. A regra de negócio impede duplicação.
- **Teste 12** — Exibe o JSON completo gerado por `paraArray()`, mostrando toda a estrutura de dados.
- **Teste 13** — Preenche 6 treinos (um por dia) e tenta adicionar um 7º. A regra de no máximo 6 treinos recusa.

## Camada web

**Uma página que existe:** `curl.exe -i "http://localhost:8000/?pagina=planos"`

```text
HTTP/1.1 200 OK
Host: localhost:8000
Date: ...
Connection: close
Content-Type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
...
```

**Uma página que não existe:** `curl.exe -i "http://localhost:8000/?pagina=inexistente"`

```text
HTTP/1.1 404 Not Found
Host: localhost:8000
Date: ...
Connection: close
Content-Type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
...
<h2>404 — Página não encontrada</h2>
...
```

**A rota JSON:** `curl.exe -i "http://localhost:8000/?formato=json"`

```text
HTTP/1.1 200 OK
Host: localhost:8000
Date: ...
Connection: close
Content-Type: application/json; charset=utf-8

{
    "nome": "FitBoa Viagem",
    "totalAlunos": 0,
    "receitaMensal": 0,
    "alunos": []
}
```

**O formulário com um dado inválido:** envio de formulário com nome vazio — a mensagem de recusa "O nome do aluno não pode ser vazio." aparece na página dentro de um div com classe `erro`.

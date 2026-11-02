# FitBoa Viagem — Sistema Academia

> Trabalho individual de Programação Web I (IFCE Campus Boa Viagem): sistema web orientado a objetos em PHP.

**Autor(a):** _(preencha)_
**Tema:** 07 — Academia
**Vídeo:** _(cole aqui o link do vídeo)_

## O problema

Academias precisam gerenciar seus alunos, planos de assinatura e treinos de forma organizada. Este sistema permite cadastrar alunos com diferentes planos (mensal, trimestral ou anual), atribuir treinos semanais a cada aluno e acompanhar a receita mensal estimada. Resolve o problema de controle manual de fichas e planilhas, oferecendo uma interface web simples e prática para donos de academia e instrutores.

## Como executar

Requisitos: PHP 8.3 ou superior.

```
git clone https://github.com/SEU-USUARIO/pweb1-trabalho-oo.git
cd pweb1-trabalho-oo
php -S localhost:8000 -t public -d display_errors=1 -d output_buffering=0
```

Abra http://localhost:8000

Para rodar o roteiro de testes no terminal:

```
php teste.php
```

## Telas

_(Coloque 2 ou 3 prints na pasta `docs/` e mostre aqui.)_

## Rotas

| URL | O que mostra | Método | Status |
|---|---|---|---|
| `/` | Página inicial com dashboard (total de alunos, receita mensal) | GET | 200 |
| `/?pagina=planos` | Tabela comparativa dos planos mensal, trimestral e anual | GET | 200 |
| `/?pagina=cadastrar` | Formulário para cadastrar novo aluno | GET | 200 |
| `/?pagina=treinos` | Formulário para adicionar treino a um aluno | GET | 200 |
| `/?pagina=alunos` | Lista de alunos com seus treinos | GET | 200 |
| `/?pagina=inexistente` | Página 404 — não encontrada | GET | 404 |
| `/?formato=json` | Dados da academia em JSON | GET | 200 |
| `/?pagina=cadastrar` (formulário) | Cadastra aluno via POST | POST | 200 |
| `/?pagina=treinos` (formulário) | Adiciona treino via POST | POST | 200 |

## Regras de negócio

| Nº | Regra | Classe e método | Mensagem da exceção |
|---|---|---|---|
| 1 | Nome do aluno não pode ser vazio | `Aluno::__construct` | O nome do aluno não pode ser vazio. |
| 2 | CPF deve ter 11 dígitos | `Aluno::__construct` | CPF inválido. Use o formato 000.000.000-00 (11 dígitos). |
| 3 | Preço-base do plano deve ser maior que zero | `Plano::__construct` | O preço-base deve ser maior que zero. |
| 4 | Máximo de 6 treinos por aluno | `Aluno::adicionarTreino` | Um aluno pode ter no máximo 6 treinos. |
| 5 | Não permitir dois treinos no mesmo dia | `Aluno::adicionarTreino` | Já existe um treino na {dia} para este aluno. |
| 6 | Dia da semana deve ser válido | `Treino::__construct` | Dia da semana inválido: {dia}. |
| 7 | Duração do treino entre 1 e 240 minutos | `Treino::__construct` | A duração do treino deve ser entre 1 e 240 minutos. |
| 8 | CPF duplicado na academia | `Academia::adicionarAluno` | Já existe um aluno cadastrado com este CPF. |

## Documentação

- [Modelagem (diagrama de classes)](docs/modelagem.md)
- [Testes](docs/testes.md)
- [Diário de uso de LLM](docs/llm.md)
- [Relatório (PDF)](docs/relatorio.pdf)

## Ferramentas utilizadas

| Ferramenta | Para que usei |
|---|---|
| PHP 8.3 | Linguagem principal do sistema, back-end e lógica OO |
| VS Code | Editor de código |
| Git e GitHub | Controle de versão e repositório remoto |
| curl | Testar rotas HTTP e a rota JSON |
| Navegador e DevTools | Testar a interface web e inspecionar respostas |
| LLM: _(qual?)_ | _(preencha)_ |
| Gravador de tela: _(qual?)_ | _(preencha)_ |

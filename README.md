# FitBoa Viagem — Sistema Academia

> Trabalho individual de Programação Web I (IFCE Campus Boa Viagem): sistema web orientado a objetos em PHP.

**Autor(a):** Ivamilton  
**Tema:** 07 — Academia (Plano: mensal, trimestral, anual | Aluno tem treinos)  
**Vídeo:** _(https://youtu.be/43gzflItFKs?is=t4g9gQZS0zFsAa07)_  
**Repositório:** [https://github.com/Ivamilton/pweb1-trabalho-oo](https://github.com/Ivamilton/pweb1-trabalho-oo)

---

## O problema

Academias de musculação e condicionamento físico precisam gerenciar com precisão seus alunos matriculados, as modalidades de planos de assinatura vigentes e a rotina semanal de treinos de cada aluno. Em muitas academias de pequeno e médio porte, esse controle é feito em fichas de papel ou planilhas desorganizadas, acarretando perda de informações, dificuldade no cálculo do faturamento mensal e impossibilidade de consultar as divisões de treino rapidamente.

O sistema **FitBoa Viagem** resolve esse problema oferecendo uma plataforma web orientada a objetos em PHP, permitindo o cadastro de alunos vinculados a planos flexíveis (mensal, trimestral com 10% de desconto ou anual com 20% de desconto), montagem da grade semanal de treinos (limitada a 6 dias e com validação de dias duplicados) e acompanhamento em tempo real da receita mensal estimada e da carga horária semanal de cada atleta.

---

## Como executar

Requisitos: PHP 8.2 ou superior (testado no PHP 8.2 e PHP 8.3).

```bash
git clone https://github.com/Ivamilton/pweb1-trabalho-oo.git
cd pweb1-trabalho-oo
php -S localhost:8000 -t public -d display_errors=1 -d output_buffering=0
```

Abra em seu navegador: [http://localhost:8000](http://localhost:8000)

Para rodar a bateria de testes automatizados no terminal:

```bash
php teste.php
```

---

## Telas

O sistema dispõe de interface web limpa, intuitiva e responsiva:

1. **Dashboard Inicial (`/?pagina=inicio`):** Exibe métricas agregadas em tempo real — número total de alunos matriculados, receita mensal estimada da academia e planos ativos.
2. **Tabela de Planos (`/?pagina=planos`):** Demonstração visual do polimorfismo, exibindo cada plano, sua duração em meses, valor total calculado com desconto e custo mensal efetivo.
3. **Cadastro de Aluno (`/?pagina=cadastrar`):** Formulário POST com validação de integridade (nome não vazio, CPF com 11 dígitos, seleção do plano de adesão).
4. **Adicionar Treino (`/?pagina=treinos`):** Formulário POST para vincular treinos (nome, dia da semana e duração em minutos) a alunos existentes, impedindo repetição no mesmo dia e limitando a até 6 treinos.
5. **Listagem de Alunos (`/?pagina=alunos`):** Visão completa de cada aluno, com CPF formatado, tipo de plano contratado, carga horária semanal acumulada e tabela detalhada de seus treinos.
6. **Página 404 (`/?pagina=inexistente`):** Tratamento amigável com cabeçalho HTTP 404 para rotas não cadastradas.
7. **Exportação JSON (`/?formato=json`):** Rota de API com `Content-Type: application/json; charset=utf-8` serializando toda a estrutura do sistema via `paraArray()`.

---

## Rotas

| URL | O que mostra | Método | Status |
|---|---|---|---|
| `/` ou `/?pagina=inicio` | Dashboard com resumo geral e métricas financeiras | GET | 200 OK |
| `/?pagina=planos` | Tabela comparativa dos planos (polimorfismo) | GET | 200 OK |
| `/?pagina=cadastrar` | Formulário para cadastro de novos alunos | GET | 200 OK |
| `/?pagina=treinos` | Formulário para adicionar treinos aos alunos | GET | 200 OK |
| `/?pagina=alunos` | Lista detalhada de alunos e seus treinos | GET | 200 OK |
| `/?pagina=inexistente` | Página de erro amigável para rotas inexistentes | GET | 404 Not Found |
| `/?formato=json` | Dados completos serializados em formato JSON | GET | 200 OK |
| `/?pagina=cadastrar` | Cadastra novo aluno no sistema | POST | 200 OK |
| `/?pagina=treinos` | Adiciona treino na grade do aluno | POST | 200 OK |

---

## Regras de negócio

O sistema implementa regras de negócio rigorosas validadas com lançamento de `Exception` e devidamente tratadas por blocos `try/catch`:

| Nº | Regra | Classe e método | Mensagem da exceção |
|---|---|---|---|
| 1 | Nome do aluno não pode ser vazio | `Aluno::__construct` | `O nome do aluno não pode ser vazio.` |
| 2 | CPF do aluno deve conter 11 dígitos numéricos | `Aluno::__construct` | `CPF inválido. Use o formato 000.000.000-00 (11 dígitos).` |
| 3 | Preço-base do plano deve ser estritamente positivo | `Plano::__construct` | `O preço-base deve ser maior que zero.` |
| 4 | Duração mínima do plano deve ser de pelo menos 1 mês | `Plano::__construct` | `A duração deve ser de pelo menos 1 mês.` |
| 5 | Desconto do plano deve estar entre 0 e 1 (0% a 100%) | `PlanoTrimestral` / `PlanoAnual` | `O desconto deve estar entre 0 e 1.` |
| 6 | Nome do treino não pode ser vazio | `Treino::__construct` | `O nome do treino não pode ser vazio.` |
| 7 | Dia da semana do treino deve ser válido | `Treino::__construct` | `Dia da semana inválido: {dia}. Use: segunda, terca, ...` |
| 8 | Duração do treino deve ser entre 1 e 240 minutos | `Treino::__construct` | `A duração do treino deve ser entre 1 e 240 minutos.` |
| 9 | Aluno pode ter no máximo 6 treinos na semana | `Aluno::adicionarTreino` | `Um aluno pode ter no máximo 6 treinos.` |
| 10 | Não permitir dois treinos no mesmo dia da semana | `Aluno::adicionarTreino` | `Já existe um treino na {dia} para este aluno.` |
| 11 | Não permitir alunos com CPF duplicado na academia | `Academia::adicionarAluno` | `Já existe um aluno cadastrado com este CPF.` |

---

## Bônus: Persistência em JSON

O sistema atende integralmente ao item bônus do trabalho acadêmico:
- Ao submeter os formulários de cadastro de aluno ou inclusão de treino, os dados são automaticamente serializados e persistidos no arquivo `data/academia.json` utilizando `file_put_contents` e `json_encode(..., JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)`.
- A cada nova requisição, a aplicação verifica a existência do arquivo com `file_exists` e o lê com `file_get_contents` e `json_decode(..., true)`, reconstruindo as instâncias de objetos em memória. Com isso, os dados cadastrados continuam persistidos mesmo após reiniciar o servidor web.

---

## Documentação

- [Modelagem de Classes (Diagrama Mermaid e justificativas)](docs/modelagem.md)
- [Relatório de Testes de Terminal e Camada Web (curl)](docs/testes.md)
- [Diário de Uso de Ferramentas LLM](docs/llm.md)
- [Relatório Final do Projeto (PDF)](docs/relatorio.pdf)

---

## Ferramentas utilizadas

| Ferramenta | Para que usei |
|---|---|
| **PHP 8.2 / 8.3** | Linguagem principal do sistema para desenvolvimento back-end orientado a objetos e servidor embutido. |
| **VS Code** | Ambiente de desenvolvimento integrado (IDE) para edição, formatação e depuração de código. |
| **Git e GitHub** | Controle de versionamento com Conventional Commits, ramificações (branches) e tags de marcos de cronograma. |
| **curl.exe** | Execução de requisições HTTP manuais via terminal para validação de cabeçalhos, status 404 e rota JSON. |
| **Navegador e DevTools** | Teste visual da interface web, submissão de formulários e inspeção de requisições de rede. |
| **Claude Opus 4.6 / Gemini 3.8 Flash** | Ferramentas de LLM utilizadas como assistentes pedagógicos na modelagem OO, projeto de testes e resolução de dúvidas. |
| **OBS Studio / Xbox Game Bar** | Software para gravação de tela e áudio da apresentação do sistema em funcionamento (até 10 minutos). |

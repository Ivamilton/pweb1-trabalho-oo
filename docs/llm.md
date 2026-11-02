# Diário de uso de LLM

Este diário documenta o processo de desenvolvimento assistido por ferramentas de inteligência artificial (LLM) no projeto **FitBoa Viagem — Sistema Academia**, atendendo às exigências pedagógicas da disciplina Programação Web I (IFCE Campus Boa Viagem).

---

## Interação 1

- **Ferramenta e data:** Claude Opus 4.6 (Thinking) — 24/10/2026
- **Prompt:**

```text
Olá! Estou desenvolvendo um sistema em PHP orientado a objetos para o IFCE Campus Boa Viagem.
O tema escolhido é o 07: Academia.
A hierarquia sugerida é Plano (mensal, trimestral, anual) e a composição sugerida é Aluno tem treinos.
Preciso modelar as classes em diagrama Mermaid atendendo aos requisitos:
1. Pelo menos 5 classes com construtor e atributos tipados;
2. Hierarquia com classe abstrata mãe e filhas com sobrescrita de método;
3. Composição de Aluno com Treinos e Academia com Alunos;
4. Encapsulamento com atributos protected/private e métodos paraArray().
Como posso estruturar essa modelagem?
```

- **Resumo da resposta:**
  A LLM propôs uma modelagem com a classe abstrata `Plano` contendo os atributos protegidos `$nome`, `$precoBase` e `$duracaoMeses`, além do método abstrato `calcularValorTotal()`. Propôs três subclasses filhas: `PlanoMensal`, `PlanoTrimestral` (com 10% de desconto) e `PlanoAnual` (com 20% de desconto). Para a composição, propôs a classe `Treino` e a classe `Aluno` (que mantém uma lista `$treinos` e o método `calcularCargaHorariaSemanal()`), além da classe `Academia` gerenciando a coleção de alunos e calculando a receita mensal total. Gerou o diagrama Mermaid com as relações de herança (`<|--`) e composição (`*--`).

- **O que aceitei, modifiquei ou rejeitei, e por quê:**
  - **Aceitei:** A separação das responsabilidades em classes e o cálculo polimórfico de desconto nas subclasses de `Plano`.
  - **Modifiquei:** A LLM havia sugerido colocar setters para todos os atributos em `Plano` e `Aluno`. Rejeitei essa sugestão porque quebra o encapsulamento de domínio (um CPF de aluno não deve ser alterado após a matrícula, e o preço/duração de um plano fixo não devem ser manipulados diretamente por setters soltos). Adicionei regras de negócio adicionais dentro dos construtores e nos métodos de adição (`adicionarTreino`).

---

## Interação 2

- **Ferramenta e data:** Claude Opus 4.6 (Thinking) — 27/10/2026
- **Prompt:**

```text
Como devo implementar o polimorfismo em PHP para atender ao requisito OO3?
O requisito diz: "um foreach que percorre objetos de classes diferentes e chama o mesmo método, cada um respondendo do seu jeito."
Mostre um exemplo aplicado aos planos da academia.
```

- **Resumo da resposta:**
  A LLM explicou que o polimorfismo ocorre quando objetos de classes diferentes (neste caso `PlanoMensal`, `PlanoTrimestral` e `PlanoAnual`), derivadas da mesma classe base `Plano`, são armazenados em um array comum e percorridos em um loop `foreach`. Dentro do laço, invoca-se `$plano->calcularValorTotal()` e `$plano->getTipo()`. Embora a chamada de método seja idêntica sintaticamente, o PHP despacha dinamicamente a execução para a implementação específica de cada subclasse filha.

- **O que aceitei, modifiquei ou rejeitei, e por quê:**
  - **Aceitei:** A estrutura do laço polimórfico demonstrando que o chamador não precisa saber o tipo exato da classe concreta em tempo de compilação.
  - **Modifiquei:** Adaptei o retorno para que a tabela HTML na rota `?pagina=planos` exiba tanto o valor total quanto o valor mensalizado rateado (`$valor / $meses`), evidenciando a vantagem financeira dos planos de maior fidelidade.

---

## Interação 3

- **Ferramenta e data:** Claude Opus 4.6 (Thinking) — 29/10/2026
- **Prompt:**

```text
Escreva a classe Treino e a validação do dia da semana e duração.
Além disso, mostre como o Aluno deve validar para não aceitar dois treinos no mesmo dia da semana e limitar a 6 treinos.
```

- **Resumo da resposta:**
  A LLM escreveu o código da classe `Treino` e a lógica em `Aluno::adicionarTreino()`. Na validação do dia da semana, sugeriu utilizar a função `mb_strtolower(trim($diaSemana))` para normalizar caracteres acentuados ou maiúsculos antes de verificar contra um array de dias aceitos. Implementou também a verificação de duplicidade percorrendo a lista de treinos já cadastrados e comparando o dia da semana.

- **O que aceitei, modifiquei ou rejeitei, e por quê:**
  - **Aceitei:** A lógica de composição e verificação de conflito de dia da semana no método `adicionarTreino`.
  - **Rejeitei/Corrigi (Erro da LLM):** A sugestão de usar `mb_strtolower` gerou uma exceção fatal de tempo de execução (`Fatal error: Uncaught Error: Call to undefined function mb_strtolower()`) no ambiente PHP CLI padrão do Windows, pois a extensão `mbstring` não estava habilitada no `php.ini`. Substituí por `strtolower(trim($diaSemana))` e padronizei os identificadores sem acento (`terca`, `sabado`), garantindo portabilidade nativa sem dependência de extensões externas.

---

## Interação 4

- **Ferramenta e data:** Gemini 3.8 Flash — 02/11/2026
- **Prompt:**

```text
Preciso estruturar a camada web no arquivo public/index.php atendendo a todos os requisitos:
- Toda lógica PHP no topo do arquivo e o HTML depois (W5);
- Tratamento de formulário POST com try/catch exibindo mensagem de exceção na página (W3);
- Rotas por query string (?pagina=...) com cabeçalho/menu unificados e status 404 (W1);
- Rota ?formato=json com header application/json; charset=utf-8 gerada por paraArray() (W2);
- htmlspecialchars em todos os dados impressos no HTML (W4);
- Bônus: persistência em arquivo JSON com file_put_contents e json_decode.
Como organizar tudo isso de forma limpa?
```

- **Resumo da resposta:**
  A LLM sugeriu a divisão do topo do arquivo em seções bem delimitadas:
  1. Carregamento das classes via `require_once`;
  2. Inicialização dos objetos e restauração do arquivo `../data/academia.json` via `json_decode`;
  3. Tratamento de requisições POST com bloco `try/catch`, persistindo o novo estado em disco via `file_put_contents`;
  4. Interceptação da rota JSON com `header('Content-Type: application/json; charset=utf-8')`, `json_encode($academia->paraArray())` e encerramento com `exit`;
  5. Validação da rota atual com `in_array()` e envio de cabeçalho `http_response_code(404)` quando inexistente;
  6. Seção HTML contendo cabeçalho fixo, navegação única, exibição de mensagens de sucesso ou erro e blocos condicionais para cada view.

- **O que aceitei, modifiquei ou rejeitei, e por quê:**
  - **Aceitei:** A divisão modular e limpa entre a camada lógica no início e a apresentação HTML ao final.
  - **Modifiquei:** Adicionei formulários dedicados tanto para matrícula de novos alunos quanto para vinculação de treinos aos alunos existentes, além de cartões de resumo estatístico no dashboard inicial.

---

## Interação 5

- **Ferramenta e data:** Gemini 3.8 Flash — 02/11/2026
- **Prompt:**

```text
Como criar um roteiro de testes completo em teste.php que comprove todas as regras de negócio sendo aceitas e recusadas, capturando exceções com try/catch e imprimindo mensagens claras no terminal?
```

- **Resumo da resposta:**
  A LLM sugeriu a criação de um script de linha de comando sequencial que testa:
  - Criação de planos válidos e polimorfismo;
  - Criação de aluno válido;
  - Tentativas de criação com parâmetros inválidos (nome vazio, CPF com dígitos insuficientes, preço-base negativo);
  - Adição de treinos válidos e cálculo da carga horária semanal;
  - Recusa de treinos no mesmo dia da semana;
  - Recusa de treinos com duração fora dos limites ou dia inválido;
  - Agrupamento em `Academia`, recusa de duplicidade de CPF e cálculo de faturamento;
  - Limite máximo de 6 treinos por aluno;
  - Demonstração do output do método `paraArray()`.

- **O que aceitei, modifiquei ou rejeitei, e por quê:**
  - **Aceitei:** Todos os cenários de teste estruturados. A clareza visual (`OK, recusado:` vs `ERRO: operação aceita`) foi fundamental para validar o sistema antes de executar os testes na camada web.

---

## O erro que a LLM cometeu

- **Interação:** Interação 3 (elaboração das classes `Treino` e `public/index.php`).
- **O que estava errado:**
  A LLM utilizou a função `mb_strtolower()` em vários pontos do código para tratar strings (como o nome dos dias da semana e os tipos de plano). Ao rodar o roteiro de testes inicial com `php teste.php`, a execução abortou imediatamente com o erro fatal:
  ```text
  Fatal error: Uncaught Error: Call to undefined function mb_strtolower() in C:\Users\sian0\Documents\pweb1-trabalho-oo\src\Treino.php:21
  ```
- **Como percebi:**
  Ao executar `php teste.php` no terminal do Windows, o interpretador PHP disparou o erro de função indefinida, evidenciando que a biblioteca `mbstring` não estava habilitada por padrão na instalação do PHP CLI.
- **Como corrigi:**
  1. No arquivo `src/Treino.php`, substituí todas as chamadas de `mb_strtolower` pela função padrão do PHP `strtolower(trim($diaSemana))`;
  2. Ajustei o vetor de dias válidos para grafias ASCII puras (`segunda`, `terca`, `quarta`, `quinta`, `sexta`, `sabado`, `domingo`), eliminando qualquer dependência de bibliotecas de manipulação multibyte externas;
  3. No arquivo `public/index.php`, revisei e substituí também as ocorrências de `mb_strtolower` por `strtolower()`;
  4. Reexecutei `php teste.php`, obtendo 100% de sucesso em todos os 13 testes sem qualquer aviso ou falha.

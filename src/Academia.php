<?php

declare(strict_types=1);

require_once __DIR__ . '/Aluno.php';

/**
 * Classe Academia — gerencia os alunos cadastrados.
 * Bônus: persiste dados em arquivo JSON.
 */
class Academia
{
    private string $nome;
    /** @var Aluno[] */
    private array $alunos = [];

    public function __construct(string $nome)
    {
        if (trim($nome) === '') {
            throw new Exception('O nome da academia não pode ser vazio.');
        }
        $this->nome = trim($nome);
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    /** @return Aluno[] */
    public function getAlunos(): array
    {
        return $this->alunos;
    }

    public function adicionarAluno(Aluno $aluno): void
    {
        // Regra: CPF duplicado
        foreach ($this->alunos as $a) {
            if ($a->getCpf() === $aluno->getCpf()) {
                throw new Exception('Já existe um aluno cadastrado com este CPF.');
            }
        }
        $this->alunos[] = $aluno;
    }

    /**
     * Calcula a receita mensal estimada (soma do valorTotal de cada plano
     * dividido pela duração em meses).
     */
    public function calcularReceitaMensal(): float
    {
        $total = 0.0;
        foreach ($this->alunos as $aluno) {
            $plano  = $aluno->getPlano();
            $total += $plano->calcularValorTotal() / $plano->getDuracaoMeses();
        }
        return round($total, 2);
    }

    public function paraArray(): array
    {
        $alunosArray = [];
        foreach ($this->alunos as $aluno) {
            $alunosArray[] = $aluno->paraArray();
        }

        return [
            'nome'           => $this->nome,
            'totalAlunos'    => count($this->alunos),
            'receitaMensal'  => $this->calcularReceitaMensal(),
            'alunos'         => $alunosArray,
        ];
    }
}

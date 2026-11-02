<?php

declare(strict_types=1);

/**
 * Representa um treino associado a um aluno.
 */
class Treino
{
    private string $nome;
    private string $diaSemana;
    private int    $duracaoMinutos;

    public function __construct(string $nome, string $diaSemana, int $duracaoMinutos)
    {
        if (trim($nome) === '') {
            throw new Exception('O nome do treino não pode ser vazio.');
        }

        $diasValidos = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
        if (!in_array(strtolower(trim($diaSemana)), $diasValidos, true)) {
            throw new Exception("Dia da semana inválido: {$diaSemana}. Use: " . implode(', ', $diasValidos) . '.');
        }

        if ($duracaoMinutos <= 0 || $duracaoMinutos > 240) {
            throw new Exception('A duração do treino deve ser entre 1 e 240 minutos.');
        }

        $this->nome            = trim($nome);
        $this->diaSemana       = strtolower(trim($diaSemana));
        $this->duracaoMinutos  = $duracaoMinutos;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getDiaSemana(): string
    {
        return $this->diaSemana;
    }

    public function getDuracaoMinutos(): int
    {
        return $this->duracaoMinutos;
    }

    public function paraArray(): array
    {
        return [
            'nome'            => $this->nome,
            'diaSemana'       => $this->diaSemana,
            'duracaoMinutos'  => $this->duracaoMinutos,
        ];
    }
}

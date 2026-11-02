<?php

declare(strict_types=1);

require_once __DIR__ . '/Plano.php';
require_once __DIR__ . '/Treino.php';

/**
 * Aluno de academia — composição: um aluno TEM uma lista de treinos.
 */
class Aluno
{
    private string $nome;
    private string $cpf;
    private Plano  $plano;
    /** @var Treino[] */
    private array  $treinos = [];

    public function __construct(string $nome, string $cpf, Plano $plano)
    {
        if (trim($nome) === '') {
            throw new Exception('O nome do aluno não pode ser vazio.');
        }

        if (!self::validarCpf($cpf)) {
            throw new Exception('CPF inválido. Use o formato 000.000.000-00 (11 dígitos).');
        }

        $this->nome  = trim($nome);
        $this->cpf   = preg_replace('/\D/', '', $cpf);
        $this->plano = $plano;
    }

    // ---------- getters ----------

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getCpfFormatado(): string
    {
        $c = $this->cpf;
        return substr($c, 0, 3) . '.' . substr($c, 3, 3) . '.'
             . substr($c, 6, 3) . '-' . substr($c, 9, 2);
    }

    public function getPlano(): Plano
    {
        return $this->plano;
    }

    /** @return Treino[] */
    public function getTreinos(): array
    {
        return $this->treinos;
    }

    // ---------- composição: adicionar treino ----------

    public function adicionarTreino(Treino $treino): void
    {
        // Regra de negócio: máximo de 6 treinos por aluno
        if (count($this->treinos) >= 6) {
            throw new Exception('Um aluno pode ter no máximo 6 treinos.');
        }

        // Regra: não permitir dois treinos no mesmo dia
        foreach ($this->treinos as $t) {
            if ($t->getDiaSemana() === $treino->getDiaSemana()) {
                throw new Exception(
                    "Já existe um treino na {$treino->getDiaSemana()} para este aluno."
                );
            }
        }

        $this->treinos[] = $treino;
    }

    // ---------- cálculo sobre a lista ----------

    /**
     * Calcula a carga horária semanal total dos treinos (em minutos).
     */
    public function calcularCargaHorariaSemanal(): int
    {
        $total = 0;
        foreach ($this->treinos as $treino) {
            $total += $treino->getDuracaoMinutos();
        }
        return $total;
    }

    // ---------- paraArray ----------

    public function paraArray(): array
    {
        $treinosArray = [];
        foreach ($this->treinos as $treino) {
            $treinosArray[] = $treino->paraArray();
        }

        return [
            'nome'                   => $this->nome,
            'cpf'                    => $this->getCpfFormatado(),
            'plano'                  => $this->plano->paraArray(),
            'treinos'                => $treinosArray,
            'cargaHorariaSemanal'    => $this->calcularCargaHorariaSemanal(),
        ];
    }

    // ---------- utilitário ----------

    private static function validarCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        return strlen($cpf) === 11;
    }
}

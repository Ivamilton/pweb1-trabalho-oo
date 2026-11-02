<?php

declare(strict_types=1);

/**
 * Classe abstrata Plano — classe mãe da hierarquia.
 * Representa um plano de academia com nome, preço-base e duração em meses.
 */
abstract class Plano
{
    protected string $nome;
    protected float  $precoBase;
    protected int    $duracaoMeses;

    public function __construct(string $nome, float $precoBase, int $duracaoMeses)
    {
        if (trim($nome) === '') {
            throw new Exception('O nome do plano não pode ser vazio.');
        }
        if ($precoBase <= 0) {
            throw new Exception('O preço-base deve ser maior que zero.');
        }
        if ($duracaoMeses <= 0) {
            throw new Exception('A duração deve ser de pelo menos 1 mês.');
        }

        $this->nome          = trim($nome);
        $this->precoBase     = $precoBase;
        $this->duracaoMeses  = $duracaoMeses;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getPrecoBase(): float
    {
        return $this->precoBase;
    }

    public function getDuracaoMeses(): int
    {
        return $this->duracaoMeses;
    }

    /**
     * Calcula o valor total do plano. Sobrescrito pelas filhas
     * para aplicar descontos específicos.
     */
    abstract public function calcularValorTotal(): float;

    /**
     * Retorna a descrição do tipo de plano. Sobrescrito pelas filhas.
     */
    abstract public function getTipo(): string;

    /**
     * Converte o objeto para array (usado na rota JSON).
     */
    public function paraArray(): array
    {
        return [
            'tipo'          => $this->getTipo(),
            'nome'          => $this->nome,
            'precoBase'     => $this->precoBase,
            'duracaoMeses'  => $this->duracaoMeses,
            'valorTotal'    => $this->calcularValorTotal(),
        ];
    }
}

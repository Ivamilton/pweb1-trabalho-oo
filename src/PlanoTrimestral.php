<?php

declare(strict_types=1);

require_once __DIR__ . '/Plano.php';

/**
 * Plano Trimestral — 10 % de desconto sobre o preço mensal × 3.
 */
class PlanoTrimestral extends Plano
{
    private float $desconto;

    public function __construct(string $nome, float $precoBase, float $desconto = 0.10)
    {
        if ($desconto < 0 || $desconto > 1) {
            throw new Exception('O desconto deve estar entre 0 e 1.');
        }
        parent::__construct($nome, $precoBase, 3);
        $this->desconto = $desconto;
    }

    public function calcularValorTotal(): float
    {
        return round($this->precoBase * $this->duracaoMeses * (1 - $this->desconto), 2);
    }

    public function getTipo(): string
    {
        return 'Trimestral';
    }

    public function getDesconto(): float
    {
        return $this->desconto;
    }
}

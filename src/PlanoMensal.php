<?php

declare(strict_types=1);

require_once __DIR__ . '/Plano.php';

/**
 * Plano Mensal — sem desconto.
 */
class PlanoMensal extends Plano
{
    public function __construct(string $nome, float $precoBase)
    {
        parent::__construct($nome, $precoBase, 1);
    }

    public function calcularValorTotal(): float
    {
        return $this->precoBase;
    }

    public function getTipo(): string
    {
        return 'Mensal';
    }
}

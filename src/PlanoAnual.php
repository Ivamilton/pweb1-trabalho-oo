<?php

declare(strict_types=1);

require_once __DIR__ . '/Plano.php';

/**
 * Plano Anual — 20 % de desconto sobre o preço mensal × 12.
 */
class PlanoAnual extends Plano
{
    private float $desconto;

    public function __construct(string $nome, float $precoBase, float $desconto = 0.20)
    {
        if ($desconto < 0 || $desconto > 1) {
            throw new Exception('O desconto deve estar entre 0 e 1.');
        }
        parent::__construct($nome, $precoBase, 12);
        $this->desconto = $desconto;
    }

    public function calcularValorTotal(): float
    {
        return round($this->precoBase * $this->duracaoMeses * (1 - $this->desconto), 2);
    }

    public function getTipo(): string
    {
        return 'Anual';
    }

    public function getDesconto(): float
    {
        return $this->desconto;
    }
}

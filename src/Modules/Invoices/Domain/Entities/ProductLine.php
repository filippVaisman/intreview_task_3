<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\ValueObjects\Money;

final readonly class ProductLine
{
    private function __construct(
        private string $name,
        private int $quantity,
        private Money $price,
        private Money $totalPrice,
    ) {
    }

    public static function create(
        string $name,
        int $quantity,
        Money $price
    ): self {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        $totalPrice = Money::fromInt($price->amount() * $quantity);

        return new self(
            $name,
            $quantity,
            $price,
            $totalPrice
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->price;
    }

    public function totalPrice(): Money
    {
        return $this->totalPrice;
    }
} 
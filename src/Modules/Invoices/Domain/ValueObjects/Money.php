<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

final readonly class Money
{
    private function __construct(
        private int $amount
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromInt(int $amount): self
    {
        return new self($amount);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }
}

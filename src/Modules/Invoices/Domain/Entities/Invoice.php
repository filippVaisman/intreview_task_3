<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Entities;

use Modules\Invoices\Application\Exceptions\InvalidInvoiceStateException;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Events\InvoiceCreatedEvent;
use Modules\Invoices\Domain\Events\InvoiceSentEvent;
use Modules\Invoices\Domain\Events\InvoiceStatusChangedEvent;
use Modules\Invoices\Domain\ValueObjects\Money;

class Invoice
{
    /** @var ProductLine[] */
    private array $productLines = [];
    private Money $totalPrice;
    private array $domainEvents = [];

    private function __construct(
        private readonly string $id,
        private readonly string $customerName,
        private readonly string $customerEmail,
        private StatusEnum $status,
    ) {
        $this->totalPrice = Money::fromInt(0);
    }

    public static function create(
        string $id,
        string $customerName,
        string $customerEmail,
    ): self {
        $invoice = new self(
            $id,
            $customerName,
            $customerEmail,
            StatusEnum::Draft
        );

        $invoice->recordEvent(new InvoiceCreatedEvent($id));

        return $invoice;
    }

    public function addProductLine(string $name, int $quantity, Money $price): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw new InvalidInvoiceStateException('Can only add product lines to draft invoices');
        }

        $productLine = ProductLine::create($name, $quantity, $price);
        $this->productLines[] = $productLine;
        $this->recalculateTotalPrice();
    }

    public function send(): void
    {
        if ($this->status !== StatusEnum::Draft) {
            throw new InvalidInvoiceStateException('Can only send draft invoices');
        }

        if (empty($this->productLines)) {
            throw new InvalidInvoiceStateException('Cannot send invoice without product lines');
        }

        foreach ($this->productLines as $line) {
            if ($line->quantity() <= 0 || $line->unitPrice()->amount() <= 0) {
                throw new InvalidInvoiceStateException('All product lines must have positive quantity and price');
            }
        }

        $this->changeStatus(StatusEnum::Sending);
        $this->recordEvent(new InvoiceSentEvent($this->id));
    }

    public function markAsSentToClient(): void
    {
        if ($this->status !== StatusEnum::Sending) {
            throw new InvalidInvoiceStateException('Can only mark sending invoices as sent');
        }

        $this->changeStatus(StatusEnum::SentToClient);
    }

    private function changeStatus(StatusEnum $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidInvoiceStateException(
                sprintf('Cannot transition from %s to %s', $this->status->value, $newStatus->value)
            );
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->recordEvent(new InvoiceStatusChangedEvent($this->id, $oldStatus, $newStatus));
    }

    private function recalculateTotalPrice(): void
    {
        $this->totalPrice = array_reduce(
            $this->productLines,
            fn (Money $carry, ProductLine $line) => $carry->add($line->totalPrice()),
            Money::fromInt(0)
        );
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    // Getters
    public function id(): string
    {
        return $this->id;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function customerEmail(): string
    {
        return $this->customerEmail;
    }

    public function status(): StatusEnum
    {
        return $this->status;
    }

    /** @return ProductLine[] */
    public function productLines(): array
    {
        return $this->productLines;
    }

    public function totalPrice(): Money
    {
        return $this->totalPrice;
    }
}

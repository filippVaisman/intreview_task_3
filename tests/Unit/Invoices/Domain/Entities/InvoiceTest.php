<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Entities;

use Modules\Invoices\Application\Exceptions\InvalidInvoiceStateException;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\Events\InvoiceCreatedEvent;
use Modules\Invoices\Domain\Events\InvoiceSentEvent;
use Modules\Invoices\Domain\Events\InvoiceStatusChangedEvent;
use Modules\Invoices\Domain\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class InvoiceTest extends TestCase
{
    private Invoice $invoice;
    private string $id;
    private string $customerName;
    private string $customerEmail;

    protected function setUp(): void
    {
        $this->id = 'test-id';
        $this->customerName = 'John Doe';
        $this->customerEmail = 'john@example.com';
        $this->invoice = Invoice::create(
            $this->id,
            $this->customerName,
            $this->customerEmail
        );
    }

    public function testCreateInvoice(): void
    {
        $events = $this->invoice->pullDomainEvents();

        $this->assertSame($this->id, $this->invoice->id());
        $this->assertSame($this->customerName, $this->invoice->customerName());
        $this->assertSame($this->customerEmail, $this->invoice->customerEmail());
        $this->assertEquals(StatusEnum::Draft, $this->invoice->status());
        $this->assertEmpty($this->invoice->productLines());
        $this->assertEquals(Money::fromInt(0), $this->invoice->totalPrice());
        $this->assertCount(1, $events);
        $this->assertInstanceOf(InvoiceCreatedEvent::class, $events[0]);
    }

    public function testAddProductLine(): void
    {
        $this->invoice->addProductLine('Product 1', 2, Money::fromInt(1000));

        $productLines = $this->invoice->productLines();
        $this->assertCount(1, $productLines);
        $this->assertEquals('Product 1', $productLines[0]->name());
        $this->assertEquals(2, $productLines[0]->quantity());
        $this->assertEquals(Money::fromInt(1000), $productLines[0]->unitPrice());
        $this->assertEquals(Money::fromInt(2000), $this->invoice->totalPrice());
    }

    public function testCannotAddProductLineToNonDraftInvoice(): void
    {
        $this->invoice->addProductLine('Product 1', 2, Money::fromInt(1000));
        $this->invoice->send();

        $this->expectException(InvalidInvoiceStateException::class);
        $this->invoice->addProductLine('Product 2', 1, Money::fromInt(500));
    }

    public function testSendInvoice(): void
    {
        $this->invoice->addProductLine('Product 1', 2, Money::fromInt(1000));
        $this->invoice->send();
        $events = $this->invoice->pullDomainEvents();

        $this->assertEquals(StatusEnum::Sending, $this->invoice->status());
        $this->assertCount(3, $events);
        $this->assertInstanceOf(InvoiceCreatedEvent::class, $events[0]);
        $this->assertInstanceOf(InvoiceStatusChangedEvent::class, $events[1]);
        $this->assertInstanceOf(InvoiceSentEvent::class, $events[2]);
    }

    public function testCannotSendInvoiceWithoutProductLines(): void
    {
        $this->expectException(InvalidInvoiceStateException::class);
        $this->invoice->send();
    }

    public function testCannotSendInvoiceWithInvalidProductLines(): void
    {
        $this->invoice->addProductLine('Product 1', 0, Money::fromInt(1000));

        $this->expectException(InvalidInvoiceStateException::class);
        $this->invoice->send();
    }

    public function testMarkAsSentToClient(): void
    {
        $this->invoice->addProductLine('Product 1', 2, Money::fromInt(1000));
        $this->invoice->send();
        $this->invoice->markAsSentToClient();

        $this->assertEquals(StatusEnum::SentToClient, $this->invoice->status());
    }

    public function testCannotMarkAsSentToClientFromDraftStatus(): void
    {
        $this->expectException(InvalidInvoiceStateException::class);
        $this->invoice->markAsSentToClient();
    }
}

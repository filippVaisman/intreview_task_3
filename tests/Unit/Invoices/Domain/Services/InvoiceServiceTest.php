<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Services;

use InvalidArgumentException;
use Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Services\InvoiceService;
use Modules\Invoices\Domain\ValueObjects\Money;
use Modules\Invoices\Infrastructure\Repositories\InvoiceRepositoryInterface;
use Modules\Notifications\Application\Services\NotificationService;
use PHPUnit\Framework\TestCase;

final class InvoiceServiceTest extends TestCase
{
    private InvoiceRepositoryInterface $repository;
    private NotificationService $notificationService;
    private InvoiceService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->service = new InvoiceService($this->repository, $this->notificationService);
    }

    public function testCreateInvoice(): void
    {
        $customerName = 'Jack London';
        $customerEmail = 'jack@example.com';
        $productLines = [
            [
                'name' => 'Product 1',
                'quantity' => 2,
                'price' => 1000,
            ],
        ];

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Invoice $invoice) use ($customerName, $customerEmail) {
                return $invoice->customerName() === $customerName
                    && $invoice->customerEmail() === $customerEmail
                    && count($invoice->productLines()) === 1
                    && $invoice->totalPrice()->equals(Money::fromInt(2000));
            }));

        $invoice = $this->service->createInvoice($customerName, $customerEmail, $productLines);

        $this->assertSame($customerName, $invoice->customerName());
        $this->assertSame($customerEmail, $invoice->customerEmail());
        $this->assertCount(1, $invoice->productLines());
    }

    public function testSendInvoice(): void
    {
        $invoice = $this->createMock(Invoice::class);
        $invoiceId = 'test-id';

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $invoice->expects($this->once())
            ->method('send');

        $invoice->expects($this->once())
            ->method('id')
            ->willReturn($invoiceId);

        $this->notificationService->expects($this->once())
            ->method('delivered')
            ->with($invoiceId);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($invoice);

        $this->service->sendInvoice($invoiceId);
    }

    public function testSendInvoiceThrowsExceptionWhenInvoiceNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(InvoiceNotFoundException::class);
        $this->service->sendInvoice('non-existent-id');
    }

    public function testMarkAsSentToClient(): void
    {
        $invoice = $this->createMock(Invoice::class);
        $invoiceId = 'test-id';

        $this->repository->expects($this->once())
            ->method('findById')
            ->with($invoiceId)
            ->willReturn($invoice);

        $invoice->expects($this->once())
            ->method('markAsSentToClient');

        $this->repository->expects($this->once())
            ->method('save')
            ->with($invoice);

        $this->service->markAsSentToClient($invoiceId);
    }

    public function testMarkAsSentToClientThrowsExceptionWhenInvoiceNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->service->markAsSentToClient('non-existent-id');
    }
}

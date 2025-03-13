<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Services;

use InvalidArgumentException;
use Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\ValueObjects\Money;
use Modules\Invoices\Infrastructure\Repositories\InvoiceRepositoryInterface;
use Modules\Notifications\Application\Services\NotificationService;
use Ramsey\Uuid\Uuid;

final readonly class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private NotificationService $notificationService
    ) {
    }

    public function createInvoice(
        string $customerName,
        string $customerEmail,
        array $productLines = []
    ): Invoice {
        $invoice = Invoice::create(
            Uuid::uuid4()->toString(),
            $customerName,
            $customerEmail
        );

        foreach ($productLines as $line) {
            $invoice->addProductLine(
                $line['name'],
                $line['quantity'],
                Money::fromInt($line['price'])
            );
        }

        $this->repository->save($invoice);

        return $invoice;
    }

    public function sendInvoice(string $invoiceId): void
    {
        $invoice = $this->repository->findById($invoiceId);
        if (!$invoice) {
            throw new InvoiceNotFoundException('Invoice not found');
        }

        $invoice->send();

        $this->repository->save($invoice);
    }

    public function markAsSentToClient(string $invoiceId): void
    {
        $invoice = $this->repository->findById($invoiceId);
        if (!$invoice) {
            throw new InvalidArgumentException('Invoice not found');
        }

        $invoice->markAsSentToClient();
        $this->repository->save($invoice);
    }
}

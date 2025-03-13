<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Handlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Invoices\Infrastructure\Repositories\InvoiceRepositoryInterface;

final readonly class ShowInvoiceHandler
{
    public function __construct(
        private InvoiceRepositoryInterface $repository
    ) {
    }

    public function __invoke(Request $request, string $invoiceId): JsonResponse
    {
        $invoice = $this->repository->findById($invoiceId);

        if (!$invoice) {
            return new JsonResponse(['error' => 'Invoice not found'], 404);
        }

        return new JsonResponse([
            'id' => $invoice->id(),
            'customer_name' => $invoice->customerName(),
            'customer_email' => $invoice->customerEmail(),
            'status' => $invoice->status()->value,
            'product_lines' => array_map(
                fn($line) => [
                    'name' => $line->name(),
                    'quantity' => $line->quantity(),
                    'price' => $line->unitPrice()->amount(),
                    'total_price' => $line->totalPrice()->amount(),
                ],
                $invoice->productLines()
            ),
            'total_price' => $invoice->totalPrice()->amount(),
        ]);
    }
}

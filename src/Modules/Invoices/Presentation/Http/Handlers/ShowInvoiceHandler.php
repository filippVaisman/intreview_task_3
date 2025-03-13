<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Handlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Invoices\Infrastructure\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Presentation\Http\Responses\ShowInvoiceResponse;

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
            return ShowInvoiceResponse::notFound();
        }

        return ShowInvoiceResponse::fromInvoice($invoice);
    }
}

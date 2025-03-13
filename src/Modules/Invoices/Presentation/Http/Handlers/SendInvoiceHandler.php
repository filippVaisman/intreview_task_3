<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Handlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Invoices\Application\Exceptions\InvalidInvoiceStateException;
use Modules\Invoices\Application\Exceptions\InvoiceNotFoundException;
use Modules\Invoices\Domain\Services\InvoiceService;
use Modules\Invoices\Presentation\Http\Responses\SendInvoiceResponse;

final readonly class SendInvoiceHandler
{
    public function __construct(
        private InvoiceService $invoiceService
    ) {
    }

    public function __invoke(Request $request, string $invoiceId): JsonResponse
    {
        try {
            $this->invoiceService->sendInvoice($invoiceId);

            return SendInvoiceResponse::success();
        } catch (InvoiceNotFoundException $e) {
            return SendInvoiceResponse::notFound($e->getMessage());
        } catch (InvalidInvoiceStateException $e) {
            return SendInvoiceResponse::invalidState($e->getMessage());
        }
    }
}

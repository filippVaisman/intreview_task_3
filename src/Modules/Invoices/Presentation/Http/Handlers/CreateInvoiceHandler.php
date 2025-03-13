<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Handlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Invoices\Domain\Services\InvoiceService;
use Modules\Invoices\Presentation\Http\Validators\CreateInvoiceValidator;

final readonly class CreateInvoiceHandler
{
    public function __construct(
        private InvoiceService $invoiceService,
        private CreateInvoiceValidator $validator
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validator->validate($request->all());
        } catch (ValidationException $e) {
            return new JsonResponse([
                'errors' => $e->errors()
            ], 422);
        }

        $invoice = $this->invoiceService->createInvoice(
            $validatedData['customer_name'],
            $validatedData['customer_email'],
            $validatedData['product_lines'] ?? []
        );

        return new JsonResponse([
            'id' => $invoice->id(),
            'status' => $invoice->status()->value,
        ], 201);
    }
}

<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\Handlers\CreateInvoiceHandler;
use Modules\Invoices\Presentation\Http\Handlers\SendInvoiceHandler;
use Modules\Invoices\Presentation\Http\Handlers\ShowInvoiceHandler;
use Ramsey\Uuid\Validator\GenericValidator;

Route::pattern('invoiceId', (new GenericValidator)->getPattern());

Route::prefix('invoices')->group(function () {
    Route::get('/{invoiceId}', ShowInvoiceHandler::class)->name('invoices.show');
    Route::post('/', CreateInvoiceHandler::class)->name('invoices.create');
    Route::post('/{invoiceId}/send', SendInvoiceHandler::class)->name('invoices.send');
});

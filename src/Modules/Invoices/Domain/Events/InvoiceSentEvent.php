<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Events;

final readonly class InvoiceSentEvent
{
    public function __construct(
        public string $invoiceId
    ) {
    }
} 
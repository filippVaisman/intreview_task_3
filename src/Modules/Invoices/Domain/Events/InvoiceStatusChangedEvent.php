<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Events;

use Modules\Invoices\Domain\Enums\StatusEnum;

final readonly class InvoiceStatusChangedEvent
{
    public function __construct(
        public string $invoiceId,
        public StatusEnum $oldStatus,
        public StatusEnum $newStatus
    ) {
    }
} 
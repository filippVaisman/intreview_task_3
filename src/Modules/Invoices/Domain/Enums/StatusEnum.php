<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Enums;

enum StatusEnum: string
{
    case Draft = 'draft';
    case Sending = 'sending';
    case SentToClient = 'sent-to-client';

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::Draft => $newStatus === self::Sending,
            self::Sending => $newStatus === self::SentToClient,
            self::SentToClient => false,
        };
    }
}

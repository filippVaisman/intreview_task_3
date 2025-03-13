<?php

use Modules\Invoices\Infrastructure\Providers\InvoicesServiceProvider;
use Modules\Notifications\Infrastructure\Providers\NotificationServiceProvider;

return [
    InvoicesServiceProvider::class,
    NotificationServiceProvider::class,
];

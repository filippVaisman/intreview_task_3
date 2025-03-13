<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Application\Listeners\ResourceDeliveredListener;
use Modules\Invoices\Infrastructure\Repositories\EloquentInvoiceRepository;
use Modules\Invoices\Infrastructure\Repositories\InvoiceRepositoryInterface;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;

final class InvoicesServiceProvider extends ServiceProvider
{
    public function __construct($app)
    {
        parent::__construct($app);
    }

    public function register(): void
    {
        $this->app->bind(
            InvoiceRepositoryInterface::class,
            EloquentInvoiceRepository::class
        );
    }

    public function boot(): void
    {
        Event::listen(
            ResourceDeliveredEvent::class,
            ResourceDeliveredListener::class
        );
    }
}

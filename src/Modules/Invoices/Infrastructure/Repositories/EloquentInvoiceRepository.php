<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Repositories;

use Modules\Invoices\Domain\Entities\Invoice as InvoiceEntity;
use Modules\Invoices\Domain\Enums\StatusEnum;
use Modules\Invoices\Domain\ValueObjects\Money;
use Modules\Invoices\Infrastructure\Models\Invoice as InvoiceModel;
use Ramsey\Uuid\Uuid;

final class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(string $id): ?InvoiceEntity
    {
        $invoice = InvoiceModel::with('productLines')->find($id);

        if (!$invoice) {
            return null;
        }

        return $this->toDomainEntity($invoice);
    }

    public function save(InvoiceEntity $invoice): void
    {
        $model = InvoiceModel::firstOrNew(['id' => $invoice->id()]);

        $model->fill([
            'customer_name' => $invoice->customerName(),
            'customer_email' => $invoice->customerEmail(),
            'status' => $invoice->status(),
        ]);

        $model->save();

        // Delete existing product lines
        $model->productLines()->delete();

        // Create new product lines
        foreach ($invoice->productLines() as $line) {
            $model->productLines()->create([
                'id' => Uuid::uuid4()->toString(),
                'name' => $line->name(),
                'quantity' => $line->quantity(),
                'price' => $line->unitPrice()->amount(),
            ]);
        }
    }

    private function toDomainEntity(InvoiceModel $model): InvoiceEntity
    {
        $invoice = InvoiceEntity::create(
            $model->id,
            $model->customer_name,
            $model->customer_email
        );

        foreach ($model->productLines as $line) {
            $invoice->addProductLine(
                $line->name,
                $line->quantity,
                Money::fromInt($line->price)
            );
        }

        // Reconstruct the invoice state
        if ($model->status !== $invoice->status()) {
            switch ($model->status) {
                case StatusEnum::Sending:
                    $invoice->send();
                    break;
                case StatusEnum::SentToClient:
                    $invoice->send();
                    $invoice->markAsSentToClient();
                    break;
                default:
                    break;
            }
        }

        return $invoice;
    }
}

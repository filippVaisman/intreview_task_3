<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Invoices\Domain\Enums\StatusEnum;

class Invoice extends Model
{
    protected $fillable = [
        'id',
        'customer_name',
        'customer_email',
        'status',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function productLines(): HasMany
    {
        return $this->hasMany(ProductLine::class);
    }
} 
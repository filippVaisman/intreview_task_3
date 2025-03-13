<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLine extends Model
{
    protected $table = 'invoice_product_lines';
    
    protected $fillable = [
        'id',
        'invoice_id',
        'name',
        'price',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
} 
<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class SendInvoiceValidator
{
    public function validate(array $data): array
    {
        return Validator::make($data, [
            'product_lines' => ['required', 'array', 'min:1'],
            'product_lines.*.quantity' => ['required', 'integer', 'min:1'],
            'product_lines.*.unit_price' => ['required', 'integer', 'min:1'],
        ])->validate();
    }
} 
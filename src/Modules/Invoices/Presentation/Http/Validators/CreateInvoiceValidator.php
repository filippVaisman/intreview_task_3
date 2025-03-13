<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class CreateInvoiceValidator
{
    public function validate(array $data): array
    {
        return Validator::make($data, [
            'customer_name' => ['required', 'string'],
            'customer_email' => ['required', 'email'],
            'product_lines' => ['present', 'array'],
            'product_lines.*.name' => ['required', 'string'],
            'product_lines.*.quantity' => ['required', 'integer', 'min:0'],
            'product_lines.*.price' => ['required', 'integer', 'min:0'],
        ])->validate();
    }
} 
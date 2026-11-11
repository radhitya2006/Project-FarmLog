<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Seeds = 'bibit';
    case Fertilizer = 'pupuk';
    case Pesticide = 'pestisida';
    case Labor = 'upah';
    case Irrigation = 'irigasi';
    case Rent = 'sewa';
    case Transport = 'transportasi';
    case Other = 'lainnya';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}

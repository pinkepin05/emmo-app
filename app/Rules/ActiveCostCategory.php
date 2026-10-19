<?php

namespace App\Rules;

use App\Models\CostCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveCostCategory implements ValidationRule
{
    public function __construct(private array $existingCategories = []) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_int($value) && ! is_string($value)) {
            $fail('Kategori biaya tidak valid.');
            return;
        }

        $detailId = explode('.', $attribute)[1] ?? '';
        // An inactive category may only be retained on its own saved detail.
        $unchanged = isset($this->existingCategories[$detailId])
            && (string) $this->existingCategories[$detailId] === (string) $value;

        $category = CostCategory::whereKey($value);
        if (! $unchanged) {
            $category->active();
        }

        if (! $category->exists()) {
            $fail('Kategori biaya tidak aktif atau tidak tersedia. Pilih kategori yang aktif.');
        }
    }
}

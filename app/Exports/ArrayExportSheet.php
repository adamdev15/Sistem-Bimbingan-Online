<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ArrayExportSheet implements FromArray, WithTitle
{
    public function __construct(
        private readonly array $rows,
        private readonly string $sheetTitle,
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        $t = preg_replace('/[\[\]\*\/\\\\?:]/', '', $this->sheetTitle) ?? $this->sheetTitle;

        return mb_substr($t, 0, 31);
    }
}

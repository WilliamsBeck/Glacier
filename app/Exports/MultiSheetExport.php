<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetExport implements WithMultipleSheets
{
    /** @param array $sheets Map [sheetTitle => arrayData] */
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        $out = [];
        foreach ($this->sheets as $title => $data) {
            $out[] = new TitledArraySheet($title, $data);
        }
        return $out;
    }
}

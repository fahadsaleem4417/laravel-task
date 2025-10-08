<?php

namespace App\Imports;

use App\Models\StockPrice;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StockPricesImport implements ToArray, WithChunkReading, ShouldQueue, WithStartRow
{

    public function array(array $rows)
    {
        $data = [];

        foreach ($rows as $row) {
            if (!isset($row[0]) || !isset($row[1])) {
                continue;
            }

            $dateValue = $row[0];

            if (is_numeric($dateValue) && $dateValue > 25569) {
                $formattedDate = Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
            } else {
               $formattedDate = $dateValue;
            }

            $data[] = [
                'date' => $formattedDate,
                'stock_price' => $row[1],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('stock_prices')->insert($data);
    }

    public function startRow(): int
    {
        return 10;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

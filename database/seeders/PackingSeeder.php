<?php

namespace Database\Seeders;

use App\Models\Packing;
use Illuminate\Database\Seeder;

class PackingSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['pakingName' => 'BOX', 'baseUnit' => 'PIECE', 'qtyInPack' => 12],
            ['pakingName' => 'BAG', 'baseUnit' => 'KG', 'qtyInPack' => 50],
            ['pakingName' => 'PAKIT', 'baseUnit' => 'GURS', 'qtyInPack' => 10],
            ['pakingName' => 'BUNDLE', 'baseUnit' => 'FEET', 'qtyInPack' => 1],
            ['pakingName' => 'CARTON', 'baseUnit' => 'PIECE', 'qtyInPack' => 24],
        ];

        foreach ($rows as $row) {
            Packing::firstOrCreate(
                [
                    'pakingName' => $row['pakingName'],
                    'baseUnit' => $row['baseUnit'],
                    'qtyInPack' => $row['qtyInPack'],
                ],
                $row
            );
        }
    }
}

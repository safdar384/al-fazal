<?php

namespace Database\Seeders;

use App\Models\PackSize;
use Illuminate\Database\Seeder;

class PackSizeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['packing_type' => 'Box', 'unit_name' => 'Piece', 'qty' => 1],
            ['packing_type' => 'Box', 'unit_name' => 'Piece', 'qty' => 12],
            ['packing_type' => 'Box', 'unit_name' => 'Dozen', 'qty' => 1],
            ['packing_type' => 'Bag', 'unit_name' => 'Kg', 'qty' => 25],
            ['packing_type' => 'Bag', 'unit_name' => 'Kg', 'qty' => 50],
            ['packing_type' => 'Pakit', 'unit_name' => 'Gurs', 'qty' => 1],
            ['packing_type' => 'Bundle', 'unit_name' => 'Feet', 'qty' => 100],
            ['packing_type' => 'Bundle', 'unit_name' => 'Kg', 'qty' => 10],
        ];

        foreach ($rows as $row) {
            PackSize::firstOrCreate(
                [
                    'packing_type' => $row['packing_type'],
                    'unit_name' => $row['unit_name'],
                    'qty' => $row['qty'],
                ],
                [
                    'label' => PackSize::makeLabel($row['packing_type'], $row['unit_name'], $row['qty']),
                ]
            );
        }
    }
}

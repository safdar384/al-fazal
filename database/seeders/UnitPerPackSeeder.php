<?php

namespace Database\Seeders;

use App\Models\UnitPerPack;
use Illuminate\Database\Seeder;

class UnitPerPackSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['unitName' => 'Piece', 'piecesPerUnit' => 1],
            ['unitName' => 'Dozen', 'piecesPerUnit' => 12],
            ['unitName' => 'Box 6', 'piecesPerUnit' => 6],
            ['unitName' => 'Box 12', 'piecesPerUnit' => 12],
            ['unitName' => 'Box 24', 'piecesPerUnit' => 24],
        ];

        foreach ($units as $unit) {
            UnitPerPack::firstOrCreate(
                ['unitName' => strtoupper($unit['unitName'])],
                ['piecesPerUnit' => $unit['piecesPerUnit']]
            );
        }

        $this->command?->info('✓ '.count($units).' unit per pack entries created successfully!');
    }
}

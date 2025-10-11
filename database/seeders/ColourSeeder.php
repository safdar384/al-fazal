<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Colour;

class ColourSeeder extends Seeder
{
    public function run(): void
    {
        $colours = [
            ['colourName' => 'Black'],
            ['colourName' => 'White'],
            ['colourName' => 'Silver'],
            ['colourName' => 'Grey'],
            ['colourName' => 'Blue'],
            ['colourName' => 'Red'],
            ['colourName' => 'Gold'],
            ['colourName' => 'Brown'],
            ['colourName' => 'Green'],
            ['colourName' => 'Yellow'],
            ['colourName' => 'Purple'],
            ['colourName' => 'Orange'],
            ['colourName' => 'Pink'],
            ['colourName' => 'Multi-Color'],
            ['colourName' => 'Beige'],
        ];

        foreach ($colours as $colour) {
            Colour::create($colour);
        }

        $this->command->info('✓ ' . count($colours) . ' colours created successfully!');
    }
}


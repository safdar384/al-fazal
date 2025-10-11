<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerArea;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            [
                'areaCode' => 'LHR',
                'areaName' => 'Lahore',
                'ajentName' => 'Muhammad Ali Khan',
            ],
            [
                'areaCode' => 'KHI',
                'areaName' => 'Karachi',
                'ajentName' => 'Ahmed Hassan',
            ],
            [
                'areaCode' => 'ISB',
                'areaName' => 'Islamabad',
                'ajentName' => 'Bilal Ahmed',
            ],
            [
                'areaCode' => 'FSD',
                'areaName' => 'Faisalabad',
                'ajentName' => 'Usman Tariq',
            ],
            [
                'areaCode' => 'RWP',
                'areaName' => 'Rawalpindi',
                'ajentName' => 'Kashif Mahmood',
            ],
            [
                'areaCode' => 'MLT',
                'areaName' => 'Multan',
                'ajentName' => 'Imran Shahid',
            ],
            [
                'areaCode' => 'SKT',
                'areaName' => 'Sialkot',
                'ajentName' => 'Farhan Malik',
            ],
            [
                'areaCode' => 'GWL',
                'areaName' => 'Gujranwala',
                'ajentName' => 'Zubair Ahmed',
            ],
            [
                'areaCode' => 'PSW',
                'areaName' => 'Peshawar',
                'ajentName' => 'Nadeem Khan',
            ],
            [
                'areaCode' => 'QTA',
                'areaName' => 'Quetta',
                'ajentName' => 'Arif Hussain',
            ],
            [
                'areaCode' => 'HYD',
                'areaName' => 'Hyderabad',
                'ajentName' => 'Shahid Afridi',
            ],
            [
                'areaCode' => 'SRG',
                'areaName' => 'Sargodha',
                'ajentName' => 'Kamran Akmal',
            ],
            [
                'areaCode' => 'BWP',
                'areaName' => 'Bahawalpur',
                'ajentName' => 'Yasir Shah',
            ],
            [
                'areaCode' => 'SHK',
                'areaName' => 'Sheikhupura',
                'ajentName' => 'Azhar Ali',
            ],
            [
                'areaCode' => 'JHL',
                'areaName' => 'Jhelum',
                'ajentName' => 'Haris Sohail',
            ],
        ];

        foreach ($areas as $area) {
            CustomerArea::create($area);
        }

        $this->command->info('✓ ' . count($areas) . ' customer areas have been created successfully!');
        $this->command->newLine();
        
        $this->command->table(
            ['Area Code', 'Area Name', 'Agent Name'],
            collect($areas)->map(function ($area) {
                return [
                    $area['areaCode'],
                    $area['areaName'],
                    $area['ajentName'],
                ];
            })->toArray()
        );
    }
}


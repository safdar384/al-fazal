<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewAgent;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = [
            [
                'ajentCode' => 'AGT001',
                'ajentName' => 'Muhammad Ali Khan',
                'AjentAdvance' => 15000.00,
                'ajentContact' => '0300-1234567',
                'ajentAdderss' => 'House 123, Street 5, Gulberg III, Lahore',
                'ajentCommission' => 5.00,
                'ajentBalance' => 25000.00,
            ],
            [
                'ajentCode' => 'AGT002',
                'ajentName' => 'Ahmed Hassan',
                'AjentAdvance' => 20000.00,
                'ajentContact' => '0321-9876543',
                'ajentAdderss' => 'Shop 45, Main Boulevard, DHA Phase 5, Karachi',
                'ajentCommission' => 4.50,
                'ajentBalance' => 35000.00,
            ],
            [
                'ajentCode' => 'AGT003',
                'ajentName' => 'Bilal Ahmed',
                'AjentAdvance' => 10000.00,
                'ajentContact' => '0333-4567890',
                'ajentAdderss' => 'Office 12, Blue Area, Islamabad',
                'ajentCommission' => 6.00,
                'ajentBalance' => 18000.00,
            ],
            [
                'ajentCode' => 'AGT004',
                'ajentName' => 'Usman Tariq',
                'AjentAdvance' => 25000.00,
                'ajentContact' => '0345-7788990',
                'ajentAdderss' => 'Plaza 7, Canal Road, Faisalabad',
                'ajentCommission' => 5.50,
                'ajentBalance' => 42000.00,
            ],
            [
                'ajentCode' => 'AGT005',
                'ajentName' => 'Kashif Mahmood',
                'AjentAdvance' => 12000.00,
                'ajentContact' => '0312-2233445',
                'ajentAdderss' => 'Market 22, Saddar, Rawalpindi',
                'ajentCommission' => 4.00,
                'ajentBalance' => 22000.00,
            ],
            [
                'ajentCode' => 'AGT006',
                'ajentName' => 'Imran Shahid',
                'AjentAdvance' => 18000.00,
                'ajentContact' => '0301-5566778',
                'ajentAdderss' => 'Building 3, University Road, Peshawar',
                'ajentCommission' => 5.25,
                'ajentBalance' => 30000.00,
            ],
            [
                'ajentCode' => 'AGT007',
                'ajentName' => 'Farhan Malik',
                'AjentAdvance' => 8000.00,
                'ajentContact' => '0322-6677889',
                'ajentAdderss' => 'House 456, Model Town, Multan',
                'ajentCommission' => 3.75,
                'ajentBalance' => 15000.00,
            ],
            [
                'ajentCode' => 'AGT008',
                'ajentName' => 'Zubair Ahmed',
                'AjentAdvance' => 22000.00,
                'ajentContact' => '0334-9988776',
                'ajentAdderss' => 'Shop 89, Mall Road, Sialkot',
                'ajentCommission' => 6.50,
                'ajentBalance' => 38000.00,
            ],
            [
                'ajentCode' => 'AGT009',
                'ajentName' => 'Nadeem Khan',
                'AjentAdvance' => 16000.00,
                'ajentContact' => '0346-1122334',
                'ajentAdderss' => 'Plaza 11, Jail Road, Gujranwala',
                'ajentCommission' => 4.75,
                'ajentBalance' => 28000.00,
            ],
            [
                'ajentCode' => 'AGT010',
                'ajentName' => 'Arif Hussain',
                'AjentAdvance' => 14000.00,
                'ajentContact' => '0313-3344556',
                'ajentAdderss' => 'Office 5, Commercial Area, Quetta',
                'ajentCommission' => 5.00,
                'ajentBalance' => 26000.00,
            ],
        ];

        foreach ($agents as $agent) {
            NewAgent::create($agent);
        }

        $this->command->info('✓ 10 agents have been created successfully!');
        $this->command->newLine();
        $this->command->table(
            ['Agent Code', 'Agent Name', 'Contact', 'Balance'],
            collect($agents)->map(function ($agent) {
                return [
                    $agent['ajentCode'],
                    $agent['ajentName'],
                    $agent['ajentContact'],
                    'Rs. ' . number_format($agent['ajentBalance'], 2),
                ];
            })->toArray()
        );
    }
}

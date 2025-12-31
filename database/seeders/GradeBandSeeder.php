<?php

namespace Database\Seeders;

use App\Models\GradeBand;
use Illuminate\Database\Seeder;

class GradeBandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradeBands = [
            [
                'code' => 'A1-C1',
                'description' => 'Non-Managerial',
                'notes' => 'Grade bands A1 through C1',
                'is_active' => true,
            ],
            [
                'code' => 'C2-D2',
                'description' => 'Management',
                'notes' => 'Grade bands C2 through D2',
                'is_active' => true,
            ],
            [
                'code' => 'D3-E5',
                'description' => 'Senior Management',
                'notes' => 'Grade bands D3 through E5',
                'is_active' => true,
            ],
            [
                'code' => 'F2',
                'description' => 'CEO and Board',
                'notes' => 'CEO and Board level',
                'is_active' => true,
            ],
        ];

        foreach ($gradeBands as $gradeBand) {
            GradeBand::firstOrCreate(
                ['code' => $gradeBand['code']],
                $gradeBand
            );
        }

        $this->command->info('Created grade bands successfully!');
    }
}

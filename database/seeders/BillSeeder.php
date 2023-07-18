<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Bill::factory()->create([
            'user_id' => '40',
            'amount' => '19899',
            'description' => 'FTTH Basic Plan Subscription',
        ]);

        \App\Models\Bill::factory()->create([
            'user_id' => '5',
            'amount' => '26875',
            'description' => 'FTTH Basic Standard Subscription',
        ]);
    }
}

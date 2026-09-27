<?php

namespace Database\Seeders;

use App\Models\Worker;
use Illuminate\Database\Seeder;

class WorkerExampleSeeder extends Seeder
{
    public function run(): void
    {
        Worker::firstOrCreate(
            [
                'first_name' => 'Demo',
                'last_name' => 'Worker',
            ],
            [
                'trade' => 'Carpenter',
                'role' => 'worker',
                'is_active' => true,
            ]
        );
    }
}
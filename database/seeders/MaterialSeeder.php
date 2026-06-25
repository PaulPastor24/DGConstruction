<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('materials')->insert([
            ['name' => 'Portland Cement (40kg)', 'category' => 'Structural', 'unit' => 'bags'],
            ['name' => '10mm Deformed Steel Bar', 'category' => 'Structural', 'unit' => 'pcs'],
            ['name' => 'Gravel (3/4")', 'category' => 'Structural', 'unit' => 'm3'],
            ['name' => 'Sand (Washed)', 'category' => 'Structural', 'unit' => 'm3'],
            ['name' => 'Ceramic Tiles (60x60)', 'category' => 'Finishing', 'unit' => 'boxes'],
            ['name' => 'Plywood (1/2")', 'category' => 'Carpentry', 'unit' => 'sheets'],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\ArchiveCenter;
use Illuminate\Database\Seeder;

class ArchiveCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centers = [
            [
                'center_code' => 'MUM',
                'description' => 'Mumbai Center',
                'region' => 'West',
                'state' => 'Maharashtra',
                'city' => 'Mumbai',
                'status' => 1,
            ],
            [
                'center_code' => 'DEL',
                'description' => 'Delhi Center',
                'region' => 'North',
                'state' => 'Delhi',
                'city' => 'New Delhi',
                'status' => 1,
            ],
            [
                'center_code' => 'BAN',
                'description' => 'Bangalore Center',
                'region' => 'South',
                'state' => 'Karnataka',
                'city' => 'Bangalore',
                'status' => 1,
            ],
            [
                'center_code' => 'CHE',
                'description' => 'Chennai Center',
                'region' => 'South',
                'state' => 'Tamil Nadu',
                'city' => 'Chennai',
                'status' => 1,
            ],
            [
                'center_code' => 'KOL',
                'description' => 'Kolkata Center',
                'region' => 'East',
                'state' => 'West Bengal',
                'city' => 'Kolkata',
                'status' => 1,
            ],
            [
                'center_code' => 'HYD',
                'description' => 'Hyderabad Center',
                'region' => 'South',
                'state' => 'Telangana',
                'city' => 'Hyderabad',
                'status' => 1,
            ],
            [
                'center_code' => 'PUN',
                'description' => 'Pune Center',
                'region' => 'West',
                'state' => 'Maharashtra',
                'city' => 'Pune',
                'status' => 1,
            ],
            [
                'center_code' => 'AHM',
                'description' => 'Ahmedabad Center',
                'region' => 'West',
                'state' => 'Gujarat',
                'city' => 'Ahmedabad',
                'status' => 1,
            ],
        ];

        foreach ($centers as $center) {
            ArchiveCenter::create($center);
        }
    }
}
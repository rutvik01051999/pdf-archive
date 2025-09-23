<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CertiStudent;

class CertiStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add some sample certificate students
        $students = [
            [
                'name' => 'John Doe',
                'mobile_number' => '9876543210',
                'created_date' => now()->subDays(5)
            ],
            [
                'name' => 'Jane Smith',
                'mobile_number' => '9876543211',
                'created_date' => now()->subDays(3)
            ],
            [
                'name' => 'Mike Johnson',
                'mobile_number' => '9876543212',
                'created_date' => now()->subDays(1)
            ],
            [
                'name' => 'Sarah Wilson',
                'mobile_number' => '9876543213',
                'created_date' => now()->subHours(5)
            ],
            [
                'name' => 'David Brown',
                'mobile_number' => '9876543214',
                'created_date' => now()->subHours(2)
            ]
        ];

        foreach ($students as $student) {
            CertiStudent::create($student);
        }
    }
}

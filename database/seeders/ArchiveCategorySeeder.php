<?php

namespace Database\Seeders;

use App\Models\ArchiveCategory;
use Illuminate\Database\Seeder;

class ArchiveCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Newspapers',
                'description' => 'Daily newspapers and publications',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 1,
            ],
            [
                'name' => 'Magazines',
                'description' => 'Weekly and monthly magazines',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 2,
            ],
            [
                'name' => 'Books',
                'description' => 'Books and publications',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 3,
            ],
            [
                'name' => 'Reports',
                'description' => 'Annual reports and documents',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 4,
            ],
            [
                'name' => 'Brochures',
                'description' => 'Marketing brochures and pamphlets',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 5,
            ],
            [
                'name' => 'Certificates',
                'description' => 'Certificates and awards',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 6,
            ],
            [
                'name' => 'Forms',
                'description' => 'Application forms and documents',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 7,
            ],
            [
                'name' => 'Manuals',
                'description' => 'User manuals and guides',
                'center_code' => null,
                'status' => 1,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            ArchiveCategory::create($category);
        }
    }
}
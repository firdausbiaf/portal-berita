<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Nasional',
                'description' => 'Berita politik, hukum, dan peristiwa terkini seputar Indonesia.',
            ],
            [
                'name' => 'Bisnis',
                'description' => 'Kabar ekonomi, pasar modal, industri, dan keuangan nasional maupun global.',
            ],
            [
                'name' => 'Olahraga',
                'description' => 'Informasi pertandingan, atlet, sepak bola, dan arena olahraga dunia.',
            ],
            [
                'name' => 'Teknologi',
                'description' => 'Perkembangan gawai, software, AI, startup, dan inovasi digital.',
            ],
            [
                'name' => 'Hiburan',
                'description' => 'Kabar selebriti, film, musik, seni, dan budaya pop.',
            ],
            [
                'name' => 'Lifestyle',
                'description' => 'Gaya hidup sehat, kuliner, wisata, dan tren terkini.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Dog', 'slug' => 'dog', 'image' => 'images/categories/dog.jpg'],
            ['name' => 'Cat', 'slug' => 'cat', 'image' => 'images/categories/cat.jpg'],
            ['name' => 'Bird', 'slug' => 'bird', 'image' => 'images/categories/bird.jpg'],
            ['name' => 'Small Animals', 'slug' => 'small-animals', 'image' => 'images/categories/hamster.jpg'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}

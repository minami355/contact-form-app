<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Category::all() as $category) {

            Contact::factory()
                ->count(4)
                ->create([
                    'category_id' => $category->id,
                ])
                ->each(function ($contact) {

                    $tags = Tag::inRandomOrder()
                        ->limit(rand(1, 3))
                        ->pluck('id');

                    $contact->tags()->attach($tags);
                });
        }
    }
}
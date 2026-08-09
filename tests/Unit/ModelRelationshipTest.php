<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * カテゴリから複数のお問い合わせを取得できる
     */
    public function test_category_has_many_contacts(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $this->assertCount(3, $category->contacts);
    }

    /**
     * お問い合わせからカテゴリを取得できる
     */
    public function test_contact_belongs_to_category(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertEquals(
            $category->id,
            $contact->category->id
        );
    }

    /**
     * お問い合わせから複数のタグを取得できる
     */
    public function test_contact_belongs_to_many_tags(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag1 = Tag::create(['name' => '質問']);
        $tag2 = Tag::create(['name' => '重要']);

        $contact->tags()->attach([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertCount(2, $contact->tags);
    }

    /**
     * タグから複数のお問い合わせを取得できる
     */
    public function test_tag_belongs_to_many_contacts(): void
    {
        $category = Category::factory()->create();

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $tag->contacts()->attach([
            $contact1->id,
            $contact2->id,
        ]);

        $this->assertCount(2, $tag->contacts);
    }
}
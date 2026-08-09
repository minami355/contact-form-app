<?php

namespace Tests\Feature\Api;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * お問い合わせ一覧APIを取得できる
     */
    public function test_can_get_contact_list(): void
    {
        // カテゴリを作成
        Category::factory()->create();

        // お問い合わせを3件作成
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);

        $response->assertJsonCount(3, 'data');
    }

    /**
     * お問い合わせ詳細を取得できる
     */
    public function test_can_get_contact_detail(): void
    {
        Category::factory()->create();

        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'email' => $contact->email,
            ],
        ]);
    }

    /**
     * お問い合わせを登録できる
     */
    public function test_can_create_contact(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => '○○ビル',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * お問い合わせを更新できる
     */
    public function test_can_update_contact(): void
    {
        // カテゴリを2件作成
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        // お問い合わせを作成
        $contact = Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        // 更新API
        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '次郎',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '09099998888',
            'address' => '大阪府大阪市',
            'building' => '△△ビル',
            'category_id' => $category2->id,
            'detail' => '更新テスト',
        ]);


        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'update@example.com',
            'category_id' => $category2->id,
        ]);
    }

    /**
     * お問い合わせを削除できる
     */
    public function test_can_delete_contact(): void
    {
        Category::factory()->create();

        $contact = Contact::factory()->create();

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(204);
        $response->assertNoContent();
    }

    /**
     * API検索のバリデーションエラー
     */
    public function test_index_validation_error(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=5');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
        ]);
    }

    /**
     * お問い合わせ登録APIのバリデーションエラー
     */
    public function test_store_validation_error(): void
    {
        Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '',
            'last_name' => '',
            'gender' => 5,
            'email' => 'abc',
            'tel' => '123',
            'address' => '',
            'category_id' => 999,
            'detail' => str_repeat('a', 121),
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }

    public function test_can_search_by_keyword(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '花子',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=太郎');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_search_by_gender(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_search_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category1->id,
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?category_id=' . $category1->id);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_search_by_date(): void
    {
        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/v1/contacts?date=' . now()->toDateString());

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_can_create_contact_with_tags(): void
    {
        $category = Category::factory()->create();

        $tag = \App\Models\Tag::create([
            'name' => '質問',
        ]);

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'tag@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => '',
            'category_id' => $category->id,
            'detail' => 'テスト',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
        ]);
    }

    public function test_can_update_contact_with_tags(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tag = \App\Models\Tag::create([
            'name' => '質問',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '更新',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'update@test.com',
            'tel' => '09099998888',
            'address' => '東京',
            'building' => '',
            'category_id' => $category->id,
            'detail' => '更新',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /**
     * タグなしでお問い合わせを更新できる
     */
    public function test_can_update_contact_without_tags(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '更新',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'new@example.com',
            'tel' => '09012345678',
            'address' => '東京',
            'building' => '',
            'category_id' => $category->id,
            'detail' => '更新テスト',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('contacts', [
            'email' => 'new@example.com',
        ]);
    }

    /**
     * 存在しないお問い合わせ詳細は404を返す
     */
    public function test_contact_detail_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }

    /**
     * 存在しないお問い合わせの更新は404を返す
     */
    public function test_contact_update_returns_404_when_not_found(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson('/api/v1/contacts/999999', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => '',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ',
        ]);

        $response->assertStatus(404);
    }

    /**
     * 存在しないお問い合わせの削除は404を返す
     */
    public function test_contact_delete_returns_404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/999999');

        $response->assertStatus(404);
    }
}
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理画面を表示できる
     */
    public function test_admin_index_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * 管理画面でキーワード検索できる
     */
    public function test_admin_can_search_contacts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $category = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '次郎',
            'last_name' => '佐藤',
            'email' => 'jiro@example.com',
        ]);

        $response = $this->get('/admin?keyword=太郎');

        $response->assertStatus(200);

        $response->assertSee('太郎');

        $response->assertDontSee('次郎');
    }

    /**
     * 管理画面でお問い合わせ詳細を表示できる
     */
    public function test_admin_can_show_contact_detail(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        $response = $this->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertSee('太郎');

        $response->assertSee('山田');

        $response->assertSee('taro@example.com');
    }

    /**
     * 管理画面でお問い合わせを削除できる
     */
    public function test_admin_can_delete_contact(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /**
     * タグを作成できる
     */
    public function test_admin_can_create_tag(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/tags', [
            'name' => '新しいタグ',
        ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('tags', [
            'name' => '新しいタグ',
        ]);
    }

    /**
     * タグ編集画面を表示できる
     */
    public function test_admin_can_show_edit_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => 'Laravel',
        ]);

        $this->actingAs($user);

        $response = $this->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);

        $response->assertSee('Laravel');
    }

    /**
     * タグを更新できる
     */
    public function test_admin_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => 'Laravel',
        ]);

        $this->actingAs($user);

        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => 'PHP',
        ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'PHP',
        ]);
    }
    /**
     * タグを削除できる
     */
    public function test_admin_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => 'Laravel',
        ]);

        $this->actingAs($user);

        $response = $this->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /**
     * 未ログインでは管理画面へアクセスできない
     */
    public function test_guest_cannot_access_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_create_tag(): void
    {
        $response = $this->post('/admin/tags', [
            'name' => 'Laravel',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * 管理画面で複数条件検索できる
     */
    public function test_admin_can_search_contacts_by_multiple_conditions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $target = Contact::factory()->create([
            'category_id' => $category1->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
            'gender' => 1,
            'created_at' => now(),
        ]);

        $target->tags()->attach($tag->id);

        Contact::factory()->create([
            'category_id' => $category2->id,
            'first_name' => '花子',
            'gender' => 2,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get('/admin?' . http_build_query([
            'keyword' => '太郎',
            'gender' => 1,
            'category_id' => $category1->id,
            'date' => now()->toDateString(),
            'tag_id' => $tag->id,
        ]));

        $response->assertStatus(200);

        $response->assertSee('太郎');
        $response->assertDontSee('花子');
    }

    /**
     * 管理画面で7件ずつページネーションされる
     */
    public function test_admin_contacts_are_paginated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create();

        Contact::factory()->count(8)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->get('/admin');

        $response->assertStatus(200);

        $contacts = $response->viewData('contacts');

        $this->assertCount(7, $contacts);
        $this->assertEquals(8, $contacts->total());
    }

    /**
     * CSVをダウンロードできる
     */
    public function test_admin_can_export_csv(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create([
            'content' => '商品のお届けについて',
        ]);

        Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'detail' => 'お問い合わせテスト',
        ]);

        $response = $this->get(route('contacts.export'));

        $response->assertStatus(200);
        $response->assertDownload('contacts.csv');
    }

    /**
     * タグ新規登録のバリデーションを確認する
     */
    public function test_admin_tag_store_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // 既存タグを作成
        Tag::create([
            'name' => 'Laravel',
        ]);

        // 未入力
        $response = $this->post('/admin/tags', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        // 51文字（max:50を超える）
        $response = $this->post('/admin/tags', [
            'name' => str_repeat('a', 51),
        ]);

        $response->assertSessionHasErrors('name');

        // 重複
        $response = $this->post('/admin/tags', [
            'name' => 'Laravel',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * タグ更新のバリデーションを確認する
     */
    public function test_admin_tag_update_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $tag = Tag::create([
            'name' => 'Laravel',
        ]);

        Tag::create([
            'name' => 'PHP',
        ]);

        // 未入力
        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        // 51文字
        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => str_repeat('a', 51),
        ]);

        $response->assertSessionHasErrors('name');

        // 他のタグと名前が重複
        $response = $this->put("/admin/tags/{$tag->id}", [
            'name' => 'PHP',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * CSVエクスポートのバリデーションを確認する
     */
    public function test_admin_export_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // 不正な性別
        $response = $this->get('/contacts/export?gender=5');

        $response->assertSessionHasErrors('gender');

        // 存在しないカテゴリID
        $response = $this->get('/contacts/export?category_id=999');

        $response->assertSessionHasErrors('category_id');

        // 不正な日付
        $response = $this->get('/contacts/export?date=invalid-date');

        $response->assertSessionHasErrors('date');
    }
}
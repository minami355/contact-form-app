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
}
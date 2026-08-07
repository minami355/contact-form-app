<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * お問い合わせ入力画面を表示できる
     */
    public function test_contact_form_can_be_displayed(): void
    {
        $category = Category::factory()->create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        $response->assertSee('商品のお届けについて');
        $response->assertSee('質問');
    }

    public function test_contact_confirm_can_be_displayed(): void
    {
        $category = Category::factory()->create();

        $response = $this->post('/contacts/confirm', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => '○○ビル',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ',
            'tag_ids' => [],
        ]);

        $response->assertStatus(200);

        $response->assertSee('太郎');
        $response->assertSee('山田');
        $response->assertSee('taro@example.com');
    }

    /**
     * 確認画面のバリデーションエラー
     */
    public function test_contact_confirm_validation_error(): void
    {
        $response = $this->from('/')
            ->post('/contacts/confirm', []);

        $response->assertRedirect('/');

        $response->assertSessionHasErrors([
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

    /**
     * お問い合わせを送信できる
     */
    public function test_contact_can_be_stored(): void
    {
        $category = Category::factory()->create();

        $tag = Tag::create([
            'name' => '質問',
        ]);

        $response = $this->post('/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => '○○ビル',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertRedirect(route('contacts.thanks'));

        $this->assertDatabaseHas('contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'tag_id' => $tag->id,
        ]);
    }

    /**
     * お問い合わせ送信時のバリデーションエラー
     */
    public function test_contact_store_validation_error(): void
    {
        $response = $this->from('/')
            ->post('/contacts', []);

        $response->assertRedirect('/');

        $response->assertSessionHasErrors([
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
}
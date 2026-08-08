<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * リクエストを許可する
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * お問い合わせフォームのバリデーションルール
     */
    public function rules(): array
    {
        return [
            // お名前
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],

            // 性別（1: 男性、2: 女性、3: その他）
            'gender' => ['required', 'integer', 'in:1,2,3'],

            // メールアドレス
            'email' => ['required', 'string', 'email', 'max:255'],

            // 電話番号（ハイフンなしの10〜11桁）
            'tel' => ['required', 'regex:/^[0-9]{10,11}$/'],

            // 住所・建物名
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],

            // お問い合わせの種類
            // categoriesテーブルに存在するIDのみ許可
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            // お問い合わせ内容（120文字以内）
            'detail' => ['required', 'string', 'max:120'],

            // タグ（複数選択可能）
            // 選択されたタグがtagsテーブルに存在するか確認
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }

    /**
     * バリデーションエラーメッセージ
     */
    public function messages(): array
    {
        return [
            // お名前
            'first_name.required' => '姓を入力してください。',
            'last_name.required' => '名を入力してください。',

            // 性別
            'gender.required' => '性別を選択してください。',

            // メールアドレス
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式で入力してください。',

            // 電話番号
            'tel.required' => '電話番号を入力してください。',
            'tel.regex' => '電話番号は10〜11桁の数字で入力してください。',

            // 住所
            'address.required' => '住所を入力してください。',

            // お問い合わせの種類
            'category_id.required' => 'お問い合わせの種類を選択してください。',

            // お問い合わせ内容
            'detail.required' => 'お問い合わせ内容を入力してください。',
            'detail.max' => 'お問い合わせ内容は120文字以内で入力してください。',
        ];
    }
}
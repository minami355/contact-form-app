<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class IndexContactRequest extends FormRequest
{
    /**
     * このリクエストを実行する権限があるか判定する
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * お問い合わせ一覧取得APIの
     * バリデーションルールを定義する
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 名前・メールアドレスの検索キーワード
            'keyword' => ['nullable', 'string', 'max:255'],

            // 性別：1（男性）、2（女性）、3（その他）のみ許可
            'gender' => ['nullable', 'integer', 'in:1,2,3'],

            // 存在するカテゴリIDのみ許可
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],

            // 日付形式のみ許可
            'date' => ['nullable', 'date'],

            // ページ番号は1以上の整数
            'page' => ['nullable', 'integer', 'min:1'],

            // 1ページあたりの件数は1〜100件
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * バリデーションエラー時の
     * 日本語メッセージを定義する
     */
    public function messages(): array
    {
        return [
            // keyword
            'keyword.string' => '検索キーワードは文字列で入力してください',
            'keyword.max' => '検索キーワードは255文字以内で入力してください',

            // gender
            'gender.integer' => '性別の値が不正です',
            'gender.in' => '性別の値が不正です',

            // category_id
            'category_id.integer' => '選択されたカテゴリーが存在しません',
            'category_id.exists' => '選択されたカテゴリーが存在しません',

            // date
            'date.date' => '日付の形式が不正です',

            // page
            'page.integer' => 'ページ番号は整数で入力してください',
            'page.min' => 'ページ番号は1以上で入力してください',

            // per_page
            'per_page.integer' => '表示件数は整数で入力してください',
            'per_page.min' => '表示件数は1以上で入力してください',
            'per_page.max' => '表示件数は100以下で入力してください',
        ];
    }
}
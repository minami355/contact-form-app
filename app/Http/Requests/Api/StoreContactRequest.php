<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    /**
     * このリクエストを実行する権限があるか判定する
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルール
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'integer', 'in:1,2,3'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'tel' => ['required', 'string', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'detail' => ['required', 'string', 'max:120'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }

    /**
     * バリデーションエラーで表示する項目名を日本語化
     */
    public function attributes(): array
    {
        return [
            'first_name' => '名',
            'last_name' => '姓',
            'gender' => '性別',
            'email' => 'メールアドレス',
            'tel' => '電話番号',
            'address' => '住所',
            'building' => '建物名',
            'category_id' => 'お問い合わせの種類',
            'detail' => 'お問い合わせ内容',
            'tag_ids' => 'タグ',
            'tag_ids.*' => 'タグ',
        ];
    }
}
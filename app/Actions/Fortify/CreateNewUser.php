<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * 入力された管理者情報をバリデーションし、
     * 問題がなければ新しいユーザーを作成する
     *
     * @param array<string, string> $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        // 管理者登録フォームの入力内容をバリデーション
        Validator::make(
            $input,
            [
                // お名前
                'name' => ['required', 'string', 'max:255'],

                // メールアドレス
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique(User::class),
                ],

                // パスワード
                'password' => $this->passwordRules(),
            ],
            [
                // お名前
                'name.required' => 'お名前を入力してください',

                // メールアドレス
                'email.required' => 'メールアドレスを入力してください',
                'email.email' => 'メールアドレスはメール形式で入力してください',

                // パスワード
                'password.required' => 'パスワードを入力してください',
                'password.min' => 'パスワードは8文字以上で入力してください',
                'password.confirmed' => 'パスワードと一致しません',
            ]
        )->validate();

        // バリデーションを通過した入力内容でユーザーを作成
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
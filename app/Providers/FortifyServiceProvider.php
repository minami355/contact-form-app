<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションサービスを登録する
     */
    public function register(): void
    {
        //
    }

    /**
     * Fortifyの認証機能を設定する
     */
    public function boot(): void
    {
        // 新規ユーザー登録時の処理を設定
        Fortify::createUsersUsing(CreateNewUser::class);

        // ユーザー情報更新時の処理を設定
        Fortify::updateUserProfileInformationUsing(
            UpdateUserProfileInformation::class
        );

        // パスワード更新時の処理を設定
        Fortify::updateUserPasswordsUsing(
            UpdateUserPassword::class
        );

        // パスワードリセット時の処理を設定
        Fortify::resetUserPasswordsUsing(
            ResetUserPassword::class
        );

        // 二要素認証時のリダイレクト処理を設定
        Fortify::redirectUserForTwoFactorAuthenticationUsing(
            RedirectIfTwoFactorAuthenticatable::class
        );

        /*
        |--------------------------------------------------------------------------
        | ログイン時のバリデーション・認証
        |--------------------------------------------------------------------------
        |
        | 要件
        | email    : 必須 / email
        | password : 必須
        |
        */
        Fortify::authenticateUsing(function (Request $request) {
            // ログインフォームの入力内容をバリデーション
            Validator::make(
                $request->all(),
                [
                    'email' => [
                        'required',
                        'email',
                    ],
                    'password' => [
                        'required',
                    ],
                ],
                [
                    'email.required' => 'メールアドレスを入力してください',
                    'email.email' => 'メールアドレスはメール形式で入力してください',
                    'password.required' => 'パスワードを入力してください',
                ]
            )->validate();

            // 入力されたメールアドレスからユーザーを検索
            $user = User::where('email', $request->email)->first();

            // ユーザーが存在し、パスワードが一致すればログイン成功
            if (
                $user &&
                Hash::check($request->password, $user->password)
            ) {
                return $user;
            }

            // 認証に失敗した場合
            return null;
        });

        // ログイン画面を表示
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 管理者登録画面を表示
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン試行を1分間に5回までに制限
        // メールアドレスとIPアドレスの組み合わせで判定する
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username()))
                . '|' . $request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        // 二要素認証の試行を1分間に5回までに制限
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->session()->get('login.id')
            );
        });
    }
}
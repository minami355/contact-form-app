<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class AdminController extends Controller
{
    /**
     * 管理画面の一覧を表示する
     */
    public function index(Request $request)
    {
        // お問い合わせデータとカテゴリ・タグ情報を取得
        $query = Contact::with(['category', 'tags']);

        // 名前またはメールアドレスで部分一致検索
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        // 性別で絞り込み
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // お問い合わせの種類で絞り込み
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 日付で絞り込み
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // タグで絞り込み
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        // お問い合わせデータを7件ずつ表示
        $contacts = $query->paginate(7);

        // 検索フォームで使用するカテゴリとタグを取得
        $categories = Category::all();
        $tags = Tag::all();

        // 管理画面にデータを渡す
        return view('admin.index', compact(
            'contacts',
            'categories',
            'tags'
        ));
    }

    /**
     * お問い合わせ詳細を表示する
     */
    public function show(Contact $contact)
    {
        // カテゴリ・タグ情報も一緒に取得
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせを削除する
     */
    public function destroy(Contact $contact)
    {
        // お問い合わせデータを削除
        $contact->delete();

        // 管理画面へ戻る
        return redirect()->route('admin.index');
    }

    /**
     * 新しいタグを登録する
     */
    public function storeTag(Request $request)
    {
        // タグ名をバリデーション
        $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:tags,name',
                ],
            ],
            [
                // 未入力の場合
                'name.required' => 'タグ名を入力してください',

                // 50文字を超えた場合
                'name.max' => 'タグ名は50文字以内で入力してください',

                // すでに同じタグ名が存在する場合
                'name.unique' => 'そのタグ名は既に使用されています',
            ]
        );

        // 新しいタグを登録
        Tag::create([
            'name' => $request->name,
        ]);

        // 管理画面へ戻る
        return redirect()->route('admin.index');
    }

    /**
     * タグ編集画面を表示する
     */
    public function editTag(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * タグを更新する
     */
    public function updateTag(Request $request, Tag $tag)
    {
        // タグ名をバリデーション
        // 現在編集中のタグ自身は重複チェックから除外する
        $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    'unique:tags,name,' . $tag->id,
                ],
            ],
            [
                // 未入力の場合
                'name.required' => 'タグ名を入力してください',

                // 50文字を超えた場合
                'name.max' => 'タグ名は50文字以内で入力してください',

                // 他のタグと同じ名前の場合
                'name.unique' => 'そのタグ名は既に使用されています',
            ]
        );

        // タグ名を更新
        $tag->update([
            'name' => $request->name,
        ]);

        // 管理画面へ戻る
        return redirect()->route('admin.index');
    }

    /**
     * タグを削除する
     */
    public function destroyTag(Tag $tag)
    {
        // タグを削除
        $tag->delete();

        // 管理画面へ戻る
        return redirect()->route('admin.index');
    }

    /**
     * 検索条件に一致するお問い合わせをCSVで出力する
     */
    public function export(Request $request)
    {
        // お問い合わせデータとカテゴリ情報を取得
        $query = Contact::with('category');

        // 名前またはメールアドレスで部分一致検索
        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('last_name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        // 性別で絞り込み
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // お問い合わせの種類で絞り込み
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 日付で絞り込み
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // タグで絞り込み
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag_id);
            });
        }

        // 検索条件に一致したデータを新着順で取得
        // 検索条件がない場合は全件を新着順で取得
        $contacts = $query
            ->orderBy('created_at', 'desc')
            ->get();

        // 要件に合わせてCSVのヘッダーを定義
        $csvHeader = [
            'ID',
            '氏名',
            '性別',
            'メール',
            '電話',
            '住所',
            '建物',
            'カテゴリ',
            '内容',
            '作成日時',
        ];

        // CSVを生成する処理
        $callback = function () use ($contacts, $csvHeader) {
            // 出力用ストリームを開く
            $handle = fopen('php://output', 'w');

            // Excelで日本語が文字化けしないようUTF-8のBOMを付与
            fwrite($handle, "\xEF\xBB\xBF");

            // CSVの1行目にヘッダーを出力
            fputcsv($handle, $csvHeader);

            // 性別の数値を表示用の文字列に変換
            $genderLabels = [
                1 => '男性',
                2 => '女性',
                3 => 'その他',
            ];

            // お問い合わせデータを1件ずつCSVに出力
            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    // ID
                    $contact->id,

                    // 姓と名を結合して氏名として出力
                    $contact->last_name . ' ' . $contact->first_name,

                    // 性別を文字列で出力
                    $genderLabels[$contact->gender] ?? '',

                    // メールアドレス
                    $contact->email,

                    // 電話番号
                    $contact->tel,

                    // 住所
                    $contact->address,

                    // 建物名
                    $contact->building,

                    // カテゴリ名
                    $contact->category->content ?? '',

                    // お問い合わせ内容
                    $contact->detail,

                    // 作成日時
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            // 出力用ストリームを閉じる
            fclose($handle);
        };

        // BOM付きCSVファイルとしてダウンロード
        return response()->streamDownload(
            $callback,
            'contacts.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
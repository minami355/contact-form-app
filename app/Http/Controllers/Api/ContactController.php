<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * お問い合わせ一覧を取得する
     */
    public function index(IndexContactRequest $request)
    {
        // カテゴリとタグを含めてお問い合わせデータを取得
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

        // お問い合わせのカテゴリで絞り込み
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 作成日で絞り込み
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 1ページあたりの表示件数を指定してページネーション
        // per_pageが指定されていない場合は20件
        $contacts = $query->paginate(
            $request->input('per_page', 20)
        );

        // ContactResourceを使用してJSON形式で返す
        return ContactResource::collection($contacts);
    }

    /**
     * 新しいお問い合わせを作成する
     */
    public function store(StoreContactRequest $request): ContactResource
    {
        // バリデーション済みのデータを使用してお問い合わせを登録
        $contact = Contact::create($request->validated());

        // タグが指定されている場合はお問い合わせとタグを紐付ける
        if ($request->filled('tag_ids')) {
            $contact->tags()->attach($request->tag_ids);
        }

        // カテゴリとタグを読み込む
        $contact->load(['category', 'tags']);

        // 作成したお問い合わせをJSON形式で返す
        return new ContactResource($contact);
    }

    /**
     * 指定されたお問い合わせの詳細を取得する
     */
    public function show(Contact $contact): ContactResource
    {
        // カテゴリとタグを含めて取得
        $contact->load(['category', 'tags']);

        // お問い合わせ詳細をJSON形式で返す
        return new ContactResource($contact);
    }

    /**
     * 指定されたお問い合わせを更新する
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): ContactResource {
        // バリデーション済みのデータでお問い合わせを更新
        $contact->update($request->validated());

        // タグが指定されている場合はタグの紐付けを更新
        if ($request->filled('tag_ids')) {
            $contact->tags()->sync($request->tag_ids);
        }

        // 更新後のカテゴリとタグを読み込む
        $contact->load(['category', 'tags']);

        // 更新後のお問い合わせをJSON形式で返す
        return new ContactResource($contact);
    }

    /**
     * 指定されたお問い合わせを削除する
     */
    public function destroy(Contact $contact)
    {
        // 指定されたお問い合わせを削除
        $contact->delete();

        // 要件に合わせて204 No Contentを返す
        // 204ではレスポンス本文を返さない
        return response()->noContent();
    }
}
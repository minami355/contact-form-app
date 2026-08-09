<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Requests\ContactRequest;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }
    public function confirm(ContactRequest $request)
    {
        $validated = $request->validated();

        $category = Category::find($validated['category_id']);

        $tags = Tag::whereIn('id', $validated['tag_ids'] ?? [])->get();

        return view('contact.confirm', compact(
            'validated',
            'category',
            'tags'
        ));
    }

    public function store(ContactRequest $request)
    {


        // 「修正」ボタンが押された場合
        if ($request->input('action') === 'back') {
            return redirect('/')
                ->withInput();
        }


        $contact = Contact::create([
            'category_id' => $request->category_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender,
            'email' => $request->email,
            'tel' => $request->tel,
            'address' => $request->address,
            'building' => $request->building,
            'detail' => $request->detail,
        ]);

        if ($request->filled('tag_ids')) {
            $contact->tags()->attach($request->tag_ids);
        }

        return redirect()->route('contacts.thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }
}
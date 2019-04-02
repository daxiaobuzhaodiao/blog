<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Post;
use App\Models\Tag;

class  PostsController  extends Controller
{
    protected $fieldList = [
        'title' => '',
        'subtitle' => '',
        'page_image' => '',
        'content' => '',
        'meta_description' => '',
        'is_draft' => '0',
        'publish_date' => '',
        'publish_time' => '',
        'layout' => 'posts.show',
        'tags' => [],
    ];

    public function index()
    {
        return view('admin.posts.index', ['posts' => Post::all()]);
    }

    public function create()
    {
        $fields = $this->fieldList;
        $when = now()->addHour();
        $fields['publish_date'] = $when->format('Y-m-d');
        $fields['publish_time'] = $when->format('g:i A');
        foreach($fields as $fieldName => $fieldValue) {
            $fields[$fieldName] = old($fieldName, $fieldValue);
        }

        $data = array_merge($fields, ['allTags' => Tag::all()->pluck('tag')->all()]);
        return view('admin.posts.create', $data);
    }

    public function store(PostCreateRequest $request)
    {
        $post = Post::create($request->postFillData());
        $post->syncTags($request->get('tags', []));
        return redirect()->route('posts.index')->with('success', '文章创建成功');
    }

    public function edit(Post $post)
    {
        $fields = $this->fieldsFromModel($post, $this->fieldList);
        foreach($fields as $fieldName => $fieldValue) {
            $fields[$fieldName] = old($fieldName, $fieldValue);
        }

        $data = array_merge($fields, ['allTags' => Tag::all()->pluck('tag')->all()]);
        return view('admin.posts.edit', $data);
    }

    public function update(PostUpdateRequest $request, Post $post)
    {
        $post->fill($request->postFillData());
        $post->save();
        $post->syncTags($request->get('tags', []));

        if ($request->action === 'continue') {
            return redirect()
                ->back()
                ->with('success', '文章已保存.');
        }
        return redirect()
            ->route('posts.index')
            ->with('success', '文章已保存.');
    }

    public function destroy(Post $post)
    {
        $post->tags()->detach();
        $post->delete();
        return redirect()->route('')->with('success', '文章已删除');
    }

    public function fieldsFromModel(Post $post, array $fields)
    {
        $fieldNames = array_keys(array_except($fields, ['tags']));
        $fields['id'] = $post->id;
        foreach ($fieldNames as $field ) {
            $fields[$field] = $post->{$field};
        }

        $fields['tags'] = $post->tags->pluck('tag')->all();

        return $fields;
    }
}

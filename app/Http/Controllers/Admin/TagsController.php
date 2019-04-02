<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Http\Requests\TagCreateRequest;
use App\Http\Requests\TagUpdateRequest;

class TagsController extends Controller
{
    protected $fields = [
        'tag' => '',
        'title' => '',
        'subtitle' => '',
        'meta_description' => '',
        'page_image' => '',
        'layout' => 'admin.posts.index',
        'reverse_direction' => 0,
    ];

    public function index()
    {
        $tags = Tag::all();
        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {   
        $data = [];
        foreach($this->fields as $field => $default) {
            $data[$field] = old($field, $default);
        }
        
        return view('admin.tags.create', $data);
    }


    public function store(TagCreateRequest $request)
    {
        $tag = new Tag();
        foreach(array_keys($this->fields) as $field) {
            $tag->$field = $request->get($field);
        }
        $tag->save();
        return redirect()->route('tags.index')->with('success', '标签'.$tag->tag.'成功');
    }


    public function show($id)
    {
        //
    }


    public function edit(Tag $tag)
    {
        $data['id'] = $tag->id;
        foreach(array_keys($this->fields) as $field) {
            $data[$field] = old($field, $tag->$field);
        }

        return view('admin.tags.edit', $data);
    }

    public function update(TagUpdateRequest $request, Tag $tag)
    {
        $tag->update($request->except('_token'));

        return redirect()->route('tags.index')->with('success', '修改已经保存');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('tags.index')->with('success', $tag->tag.'标签删除成功');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Services\PostService;
use App\Models\Tag;

class PostsController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->tag);
        $tag = $request->input('tag');
        $postService = new PostService($tag);
        $data = $postService->lists();
        $res = Tag::layout($tag);
        $layout = $tag ? $res : 'posts.index';
        return view($layout, $data);
    }
    
    public function show(Request $request, $slug)
    {
        $post = Post::with('tags')->where('slug', $slug)->firstOrFail();
        $tag = $request->input('tag');
        if($tag) {
            $tag = Tag::where('tag', $tag)->firstOrFail();
        }

        return view($post->layout, compact('post', 'tag'));
        // return view('posts.show', compact('post', 'tag'));
    }
}

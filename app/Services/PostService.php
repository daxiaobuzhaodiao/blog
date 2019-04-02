<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Carbon\Carbon;

class PostService
{
    protected $tag;

    /**
     * 控制器
     * 
     * @param string/null $tag
     */
    public function __construct($tag)
    {   
        $this->tag = $tag;
    }

    /**
     * 如果传入标签， 则展示含有该标签的文章列表， 否则返回正常的所有文章列表
     * 
     * @return array
     */
    public function lists()
    {
        if($this->tag) {
            return $this->TagIndexData($this->tag);
        }
        return $this->normalIndexData();
    }

    /**
     * return data for normal index page
     * 
     * @return array
     */
    public function normalIndexData()
    {
        $posts = Post::with('tags')
            ->where('published_at', '<=', now())
            ->where('is_draft', false)
            ->orderBy('published_at', 'desc')
            ->paginate(config('blog.posts_per_page'));
        return [
            'title' => config('blog.title'),
            'subtitle' => config('blog.subtitle'),
            'posts' => $posts,
            'page_image' => config('blog.page_image'),
            'meta_description' => config('blog.meta_description'),
            'reverse_direction' => false,
            'tag' => null
        ];
    }

    /**
     * return data for a tag index page
     * 
     * @param string $tag
     * @return array
     */
    public function tagIndexData($tag)
    {
        $tag = Tag::where('tag', $tag)->firstOrFail();
        $reverse_direction = (bool)$tag->reverse_direction;
        $posts = Post::where('published_at', '<=', now())
            ->whereHas('tags', function($query) use($tag) {
                $query->where('tag', $tag->tag);
            })
            ->where('is_draft', 0)
            ->orderBy('published_at', $reverse_direction ? 'asc' : 'desc')
            ->paginate(config('blog.posts_per_page'));
        $posts->appends('tag', $tag->tag);

        $page_image = $tag->page_image ? : config('blog.page_image');
        return [
            'title' => $tag->title,
            'subtitle' => $tag->subtitle,
            'posts' => $posts,
            'page_image' => $page_image,
            'tag' => $tag,
            'reverse_direction' => $reverse_direction,
            'meta_description' => $tag->meta_description ?: config('blog.description')
        ];
    }
}
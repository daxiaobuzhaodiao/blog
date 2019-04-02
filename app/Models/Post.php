<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Markdown;
use App\Services\Markdowner;
use App\Models\Tag;
use Carbon\Carbon;

class Post extends Model
{
    protected $dates = ['published_at'];

    // protected $fillable = ['title', 'subtitle', 'slug', 'content', 'published_at'];
    protected $guarded = [];

    // 数据入库前根据 title 的值，赋值给 slug
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        if(! $this->exists) {
            $this->attributes['slug'] = str_slug($value);
        }
    }

    // 关联 tag 模型，多对多
    function tags()
    {
        return $this->belongsToMany('App\Models\Tag', 'post_tag_pivot');
    }

    /**
     * 通过回调的方法设置唯一的 slug
     * 
     * recursive routine to set a unique slug
     * @param string $title
     * @param mixed $extra
     */
    public function setUniqueSlug($title, $extra)
    {
        $slug = str_slug($title, '-', $extra);
        if(static::where('slug', $slug)->exists()) {
            // 发起回调
            $this->setUniqueSlug($title, '-', $extra + 1);
            return;
        }
        $this->attributes['slug'] = $slug;
    }

    /**
     * set the html content automatically when the raw content is set
     * 
     * @param string $value
     */
    public function setContentRawAttribute($value)
    {
        $markdown = new Markdowner();
        
        $this->attributes['content_raw'] = $value;
        $this->attributes['content_html'] = $markdown->toHTML($value);
    }

    /**
     * sync tag relation adding new tags as needed
     * 
     * @param array $tags
     */
    public function syncTags(array $tags)
    {
        // dd($tags);
        Tag::addNeededTags($tags);
        
        if(count($tags)) {
            $this->tags()->sync(
                Tag::whereIn('tag', $tags)->get()->pluck('id')->all()
            );
            return;
        }
        
        $this->tags()->detach();
    }

    // 返回 published_at 的日期部分
    public function getPublishDateAttribute($value)
    {
        return $this->published_at->format('Y-m-d');
    }
    // 返回 published_at 的时间部分
    public function getPublishTimeAttribute($value)
    {
        return $this->published_at->format('g:i A');
    }

    /**
     * content raw 字段名称
     */
    public function getContentAttribute($value)
    {
        return $this->content_raw;
    }

    /**
     * Return URL to post
     *
     * @param Tag $tag
     * @return string
     */
    public function url(Tag $tag = null)
    {
        $url = url('posts/' . $this->slug);
        if ($tag) {
            $url .= '?tag=' . urlencode($tag->tag);
        }

        return $url;
    }

    /**
     * Return array of tag links
     *
     * @param string $base
     * @return array
     */
    public function tagLinks($base = '/posts?tag=%TAG%')
    {
        $tags = $this->tags()->get()->pluck('tag')->all();
        $return = [];
        foreach ($tags as $tag) {
            $url = str_replace('%TAG%', urlencode($tag), $base);
            $return[] = '<a href="' . $url . '">' . e($tag) . '</a>';
        }
        return $return;
    }

    /**
     * Return next post after this one or null
     *
     * @param Tag $tag
     * @return Post
     */
    public function newerPost(Tag $tag = null)
    {
        $query =
            static::where('published_at', '>', $this->published_at)
                ->where('published_at', '<=', Carbon::now())
                ->where('is_draft', 0)
                ->orderBy('published_at', 'asc');
        if ($tag) {
            $query = $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('tag', '=', $tag->tag);
            });
        }

        return $query->first();
    }

    /**
     * Return older post before this one or null
     *
     * @param Tag $tag
     * @return Post
     */
    public function olderPost(Tag $tag = null)
    {
        $query =
            static::where('published_at', '<', $this->published_at)
                ->where('is_draft', 0)
                ->orderBy('published_at', 'desc');
        if ($tag) {
            $query = $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('tag', '=', $tag->tag);
            });
        }

        return $query->first();
    }
}

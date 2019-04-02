<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'tag', 'title', 'subtitle', 'page_image', 
        'meta_description', 'layout', 'reverse_direction'
    ];

    /**
     * 文章与标签属于 多对多关系
     */
    public function posts()
    {
        return $this->belongsToMany('App\Models\Post', 'post_tag_pivot');
    }

    /**
     * add any tags needed from list
     * @param array $tags list of tags to check/add
     */
    public static function addNeededTags(array $tags)
    {
        if(count($tags) === 0) {
            return;
        }
        $found = static::whereIn('tag', $tags)->get()->pluck('tag')->all();
        
        $res = array_diff($tags, $found);   // 返回在数组 1 中， 但不在数组 2 中的元素组成的数组
        foreach ($res as $tag) {
            static::create([
                'tag' => $tag,
                'title' => $tag,
                'subtitle' => 'Subtitle for '.$tag,
                'page_image' => '',
                'meta_description' => '',
                'reverse_direction' => false
            ]);
        }
    }

    /**
     * Return the index layout to use for a tag
     *  返回标签的布局，如果对应标签值不存在或者没有布局，返回默认值
     * @param string $tag
     * @param string $default
     * @return string
     */
    public static function layout($tag, $default = 'posts.index')
    {
        $layout = static::where('tag', $tag)->get()->pluck('layout')->first();
        return $layout ?: $default;
    }
    
}

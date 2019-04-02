<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tag')->unique();        // 名称
            $table->string('title');                // 标题
            $table->string('subtitle');             // 子标题
            $table->string('page_image');           // 标签图片
            $table->string('meta_description');     // 标签介绍
            $table->string('layout')->default('posts.index');  // 博客布局
            $table->boolean('reverse_direction');   // 文章列表按时间升序排列，默认是降序
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
}

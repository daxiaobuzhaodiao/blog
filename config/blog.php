<?php

    return [
        'name' => 'laravel-blog',
        'title' => 'blog',
        'subtitle' => 'https://blog.luke88.top',
        'description' => 'laravel-blog',
        'author' => 'luke',
        'page_image' => 'home-bg.jpg',
        'posts_per_page' => 10,

        'uploads' => [
            'storage' => 'public',  // 对应的是 fileSystems.php 中的 disks 中的 public
            'webPath' => '/storage/uploads',
        ]
    ];

    
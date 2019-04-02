<?php

Route::get('/', function () {
    return redirect('/posts');
});

Route::get('posts', 'PostsController@index')->name('posts.index');
Route::get('posts/{slug}', 'PostsController@show')->name('posts.show');


// 后台路由
Route::namespace('Admin')->group(function() {
    Route::resource('admin/posts', 'PostsController', ['except' => ['show']]);
    Route::resource('admin/tags', 'TagsController', ['except' => ['show']]);
    Route::get('admin/upload', 'UploadController@index')->name('admin.upload.index');
    Route::post('admin/upload/file', 'UploadController@uploadFile')->name('admin.upload.uploadfile');
    Route::delete('admin/upload/file', 'UploadController@deleteFile')->name('admin.upload.deletefile');
    Route::post('admin/upload/folder', 'UploadController@createFolder')->name('admin.upload.createfolder');
    Route::delete('admin/upload/folder', 'UploadController@deleteFolder')->name('admin.upload.deletefolder');

});
// 后台登录退出
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('/login', 'Auth\LoginController@login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

// 联系我
Route::get('contact', 'ContactController@showForm')->name('contact.form');
Route::post('contact', 'ContactController@sendContactInfo')->name('contact.info');
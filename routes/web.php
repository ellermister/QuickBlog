<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});
Route::get('/blog', function () {
    return "test";
});

Route::get('/admin/platforms', 'Admin\PlatformsController@showListPage');
Route::get('/admin/post', 'Admin\PostController@showPostList');
Route::get('/admin/post/new', 'Admin\PostController@showEditorPage');
Route::post('/admin/post/new', 'Admin\PostController@newPostInstance');

Route::get('/admin/post/{id}', 'Admin\PostController@showEditorPageWithPost');
Route::post('/admin/post/{id}', 'Admin\PostController@updatePostInstance');

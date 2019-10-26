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


Route::get('/admin/category', 'Admin\CategoryController@showListPage');
Route::get('/admin/category/new', 'Admin\CategoryController@showEditorPage');
Route::post('/admin/category/new', 'Admin\CategoryController@newCategoryInstance');

Route::get('/admin/category/{id}', 'Admin\CategoryController@showEditorPageWithCategory');
Route::post('/admin/category/{id}', 'Admin\CategoryController@updateCategoryInstance');
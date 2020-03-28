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

Route::get('/', 'PostsController@showIndexPage');
Route::get('/latest', 'PostsController@showLatestPosts');
Route::get('/hots', 'PostsController@showHotsPosts');
Route::get('/category/{id}', 'PostsController@showCategoryPosts');
Route::get('/post/{id}', 'PostsController@showPostDetail');
Route::get('/archive/{date}', 'PostsController@showArchiveList');
Route::get('/about', 'AboutController@showPage');

Route::get('/login', 'LoginController@showLoginPage')->name('login');
Route::post('/login', 'LoginController@verifyLogin');

Route::group(['prefix' => 'admin', 'middleware' => ['web','auth']], function (){
    Route::get('/platforms', 'Admin\PlatformsController@showListPage')->name('dashboard');
    Route::get('/platforms/{id}/account', 'Admin\PlatformsController@showAccountPage');
    Route::post('/platforms/{id}/account', 'Admin\PlatformsController@updateAccount');
    Route::get('/platforms/{id}/sync', 'Admin\PlatformsController@createSchemes');
    Route::post('/platforms/{id}/category/union', 'Admin\PlatformsController@createUnionCategory');
    Route::get('/platforms/{id}/category/union', 'Admin\PlatformsController@getUnionCategoryList');

    Route::get('/post/{id}/featured', 'Admin\PostController@ActiveFeatured');
    Route::get('/post', 'Admin\PostController@showPostList');
    Route::get('/post/new', 'Admin\PostController@showEditorPage');
    Route::post('/post/new', 'Admin\PostController@newPostInstance');

    Route::get('/post/{id}', 'Admin\PostController@showEditorPageWithPost');
    Route::post('/post/{id}', 'Admin\PostController@updatePostInstance');
    Route::delete('/post/{id}', 'Admin\PostController@deletePostInstance');

    Route::post('/upload', 'Admin\PostController@uploadImage');


    Route::get('/category', 'Admin\CategoryController@showListPage');
    Route::get('/category/new', 'Admin\CategoryController@showEditorPage');
    Route::post('/category/new', 'Admin\CategoryController@newCategoryInstance');

    Route::get('/category/{id}', 'Admin\CategoryController@showEditorPageWithCategory');
    Route::post('/category/{id}', 'Admin\CategoryController@updateCategoryInstance');

    Route::get('/log', 'Admin\SchemesLogController@showLog');
    Route::get('/log/clear', 'Admin\SchemesLogController@clearLog');

    Route::get('/helper', 'Admin\HelperController@showInfo');

    Route::get('/setting', 'Admin\SettingController@showSettingPage');
    Route::post('/setting', 'Admin\SettingController@updateSetting');
});


<?php

namespace App\Http\Controllers\Admin;

use App\Model\Platforms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlatformsController extends Controller
{
    /**
     * 显示平台列表
     * @param Request $request
     */
    public function showListPage(Request $request)
    {
        $platforms = Platforms::getList();
        return view('admin.platforms', ['platforms' =>  $platforms]);
    }
}

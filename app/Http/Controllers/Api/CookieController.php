<?php

namespace App\Http\Controllers\Api;

use App\Model\Platforms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CookieController extends Controller
{
    public function receivePluginCookie(Request $request)
    {
        $param = $request->only(['name', 'cookie']);
        if (empty($param['name']) || empty($param['cookie'])) {
            return eeJson('name,cookie 不能为空', 500);
        }
        $platform = Platforms::getPlatformInfo($param['name']);
        if(!$platform){
            abort(404);
        }
        if ($platform->verifyAndSetCookie($param['cookie'])) {
            return eeJson('ok', 200);
        }
        return eeJson('COOKIE收到无效', 500);
    }
}

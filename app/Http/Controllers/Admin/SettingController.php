<?php

namespace App\Http\Controllers\Admin;

use App\Model\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function showSettingPage()
    {
        $setting = Setting::getSetting();
        return view('admin.setting',['setting' => $setting]);
    }

    public function updateSetting(Request $request)
    {
        $param = $request->only( ['site_name','site_keyword','site_describe']);
        foreach($param as $name => $value){
            $option = new Setting();
            $option->name = strtolower($name);
            $option->value = $value;
            $option->save();
        }
        return redirect()->back();
    }

}

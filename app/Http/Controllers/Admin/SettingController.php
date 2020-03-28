<?php

namespace App\Http\Controllers\Admin;

use App\Model\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        $adminPassword = $request->input('admin_password');
        if(!empty($adminPassword)){
            $user = Auth::user();
            $user->changePassword($adminPassword);
        }


        foreach($param as $name => $value){
            $option = Setting::firstOrNew(['name' => $name]);
            $option->name = strtolower($name);
            $option->value = $value;
            $option->save();
        }
        return redirect()->back();
    }

}

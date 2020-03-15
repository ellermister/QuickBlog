<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SchemesLogController extends Controller
{
    //

    public function showLog(Request $request)
    {
        $path = $this->getLogPath();
        if(is_file($path)){
            $logText = File::get($path);
        }
        return view('admin.log', ['logText' => $logText ?? '']);
    }

    public function clearLog(Request $request)
    {
        $path = $this->getLogPath();
        File::put($path,'');
        return redirect()->back();
    }

    protected function getLogPath()
    {
        $config = config('logging.channels.schemes');
        $path = $config['path'];
        if($config['driver'] == 'daily'){
            $pos = strrpos($path, '.');
            $path = substr($path, 0,$pos).date('-Y-m-d').substr($path,$pos);
        }elseif($config['driver'] == 'single'){
            $path = $config['path'];
        }
        return $path;
    }
}

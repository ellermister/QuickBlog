<?php

namespace App\Http\Middleware;

use Closure;

class PluginSync
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($token = $request->get('token')){
            if(trim(strtolower($token)) == trim(strtolower(env('TOKEN')))){
                return $next($request);
            }
        }
        return response(eeJson('TOKEN验证失败', 400));
    }
}

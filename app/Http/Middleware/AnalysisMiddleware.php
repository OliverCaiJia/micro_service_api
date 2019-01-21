<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\Logger\SLogger;
use \Illuminate\Http\Request;

class AnalysisMiddleware
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
        // 记录用户请求终端类型
        $device = $request->header('X-Device');
        $ua = $request->header('User-Agent');       
               
        return $next($request);
    }

    public function terminate($request, $response)
    {
        
    }

}

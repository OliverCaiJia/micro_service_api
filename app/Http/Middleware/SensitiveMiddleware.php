<?php

namespace App\Http\Middleware;

use App\Helpers\RestUtils;
use App\Strategies\CommentStrategy;
use Closure;
use App\Helpers\RestResponseFactory;
use Illuminate\Http\Request;

/**
 * 敏感词
 */
class SensitiveMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //接收值
        $content = $request->input('content','');
        //过滤
        $res = CommentStrategy::sensitiveWordFilter($content);

        if($res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(1607),1607);
        }

        return $next($request);
    }

}

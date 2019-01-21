<?php

namespace App\Http\Middleware;

use App\Helpers\RestUtils;
use Closure;
use App\Helpers\RestResponseFactory;
use \Illuminate\Http\Request;

class ValidateMiddleware
{

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Validators';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $validator = null)
    {
        $apiVersion = $request->segment(1);
        if ($validator)
        {
            $validator = $this->namespace . '\\' . studly_case($apiVersion) . '\\' . studly_case($validator) . 'Validator';
            $validator = new $validator($request->all());

            if (!$validator->passes())
            {
                $errorCode = 99;
                $validatorKey = '';
                foreach ($validator->getValidator()->failed() as $key => $val)
                {
                    $validatorKey .= $key . '.' . array_keys($val)[0];
                    break;
                }
                foreach ($validator->getCodes() as $key => $code)
                {
                    if (strcasecmp($validatorKey, $key) == 0)
                    {
                        $errorCode = $code;
                        break;
                    }
                }
                return RestResponseFactory::ok(RestUtils::getStdObj(), $validator->errors()->first(), $errorCode, $validator->errors()->first());
            }
        }
        return $next($request);
    }

    public function terminate($request, $response)
    {
        // 请求结束干的事情..
    }

}

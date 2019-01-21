<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Helpers\RestResponseFactory;
use Log;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
	    MethodNotAllowedHttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //记录异常
        Log::error($e);
        if ($e instanceof NotFoundHttpException)
        {
            return RestResponseFactory::notfound('Not Found');
        }
        elseif ($e instanceof ModelNotFoundException)
        {
            return RestResponseFactory::notfound($e->getMessage() ?: 'Model Not Found', 9, $e->getMessage());
        }
        elseif ($e instanceof BadRequestHttpException)
        {
            return RestResponseFactory::badrequest('出错啦,请重试(400)', 9, '出错啦,请重试(400)');
        }
        elseif ($e instanceof HttpException)
        {
            if ($e->getStatusCode() == 503)
            {
                return RestResponseFactory::any(null, 503, '系统维护中, 请稍后访问', 9, '系统维护中, 请稍后访问');
            }
        }
        elseif ($e instanceof FatalErrorException)
        {
            return RestResponseFactory::error('出错啦,请重试, 或联系客服(500)', 9, '出错啦,请重试, 或联系客服(500)');
        }
        elseif ($e instanceof MethodNotAllowedHttpException)
        {
            return RestResponseFactory::any(null, 405, 'Http Method Not Allowed', 9, 'Http Method Not Allowed');
        }
        else
        {
             return RestResponseFactory::badrequest($e->getMessage() ? $e->getMessage() : 'API Error');
        }
        return parent::render($request, $e);
    }

}

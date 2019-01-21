<?php

require_once __DIR__ . '/../vendor/autoload.php';

try
{
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
}
catch (Dotenv\Exception\InvalidPathException $e)
{
    //
}

/**
  |--------------------------------------------------------------------------
  | Create The Application
  |--------------------------------------------------------------------------
  |
  | Here we will load the environment and create the application instance
  | that serves as the central piece of this framework. We'll use this
  | application as an "IoC" container and router for this framework.
  |
 */
$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();
$app->withEloquent();

/**
  |--------------------------------------------------------------------------
  | Register Container Bindings
  |--------------------------------------------------------------------------
  |
  | Now we will register a few bindings in the service container. We will
  | register the exception handler and the console kernel. You may add
  | your own bindings here if you like or you can make another file.
  |
 */
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class, App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class, App\Console\Kernel::class
);

/**
  |--------------------------------------------------------------------------
  | Register Middleware
  |--------------------------------------------------------------------------
  |
  | Next, we will register the middleware with the application. These can
  | be global middleware that run before and after each request into a
  | route or middleware that'll be assigned to some specific routes.
  |
 */
$app->middleware([
    \Barryvdh\Cors\HandleCors::class
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'validate' => App\Http\Middleware\ValidateMiddleware::class,
    'analysis' => App\Http\Middleware\AnalysisMiddleware::class,
    'sign' => App\Http\Middleware\SignMiddleware::class,
    'api_cros' => App\Http\Middleware\CrossMiddleware::class,
    'cros' => \Barryvdh\Cors\HandleCors::class,
    //敏感词过滤
    'sensitive' => App\Http\Middleware\SensitiveMiddleware::class
]);
/**
  |--------------------------------------------------------------------------
  | Register Service Providers
  |--------------------------------------------------------------------------
  |
  | Here we will register all of the application's service providers which
  | are used to bind services into the container. Service providers are
  | totally optional, so you are not required to uncomment this line.
  |
 */
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Framework Service Providers
  |--------------------------------------------------------------------------
 */
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Third Service Providers
  |--------------------------------------------------------------------------
 */
$app->register(Mnabialek\LaravelSqlLogger\Providers\ServiceProvider::class);
$app->register(SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class);
$app->register(Barryvdh\Cors\ServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Jenssegers\Agent\AgentServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Laravel Service Providers
  |--------------------------------------------------------------------------
 */
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Illuminate\Routing\RoutingServiceProvider::class);

/**
  |--------------------------------------------------------------------------
  | Register Laravel Face Aliases
  |--------------------------------------------------------------------------
 */
$app->withAliases([
    SimpleSoftwareIO\QrCode\Facades\QrCode::class => 'QrCode',
    Jenssegers\Agent\Facades\Agent::class => 'Agent'
]);

/**
  |--------------------------------------------------------------------------
  | Bind Laravel Manager
  |--------------------------------------------------------------------------
 */
$app->bind(\Illuminate\Cache\CacheManager::class, function ($app) {
    return new \Illuminate\Cache\CacheManager($app);
});
/**
  |--------------------------------------------------------------------------
  | Register Lumen Config
  |--------------------------------------------------------------------------
 */
$app->configure('cors');        // 跨域请求
$app->configure('captcha');     // 二维码
$app->configure('sms');         // 短信
$app->configure('sudai');       // 速贷配置


/**
 *  判断当前是生产环境
 */
define("PRODUCTION_ENV", (env('APP_ENV') == 'production'));
/**
 * 辅助全局函数
 */
require __DIR__ . '/helpers.php';
/**
  |--------------------------------------------------------------------------
  | Load The Application Routes
  |--------------------------------------------------------------------------
  |
  | Next we will include the routes file so that they can all be added to
  | the application. This will provide all of the URLs the application
  | can respond to, as well as the controllers that may handle them.
  |
 */
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

date_default_timezone_set('Asia/Shanghai'); //时区配置

return $app;

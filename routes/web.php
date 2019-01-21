<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

$router->get('/', function () use ($router) {
    return \App\Helpers\RestResponseFactory::ok(null, 'jdt API');
});
// V1版本接口
$router->get('/v1', function () {
    return \App\Helpers\RestResponseFactory::ok(null, 'jdt API v1.0');
});
// V2版本接口
$router->get('/v2', function () {
    return \App\Helpers\RestResponseFactory::ok(null, 'jdt API v2.0');
});
// V3版本接口
$router->get('/v3', function () {
    return \App\Helpers\RestResponseFactory::ok(null, 'jdt API v3.0');
});
// V4版本接口
$router->get('/v4', function () {
    return \App\Helpers\RestResponseFactory::ok(null, 'jdt API v4.0');
});
// V5版本接口
$router->get('/v5', function () {
    return \App\Helpers\RestResponseFactory::ok(null, 'jdt API v5.0');
});
/**
 * Load all routes
 */
foreach (app()->make('files')->allFiles(__DIR__ . '/api') as $partial)
{
    require_once $partial->getPathname();
}
foreach (app()->make('files')->allFiles(__DIR__ . '/view') as $partial)
{
    require_once $partial->getPathname();
}
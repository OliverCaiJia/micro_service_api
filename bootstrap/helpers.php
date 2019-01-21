<?php

use App\Helpers\RestResponseFactory;

if (! function_exists('makeSuccessMsg')) {

    function makeSuccessMsg($message)
    {
        return RestResponseFactory::ok($message);
    }
}
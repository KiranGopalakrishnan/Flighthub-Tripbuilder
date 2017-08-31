<?php

// Application middleware

//use Psr7Middlewares\Middleware\TrailingSlash;
//CORS allow origin headers for all requests
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

//$app->add(new TrailingSlash(false)); // true adds the trailing slash (false removes it)

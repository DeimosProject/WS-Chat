<?php

namespace Deimos\WebSocket\Controller;

/**
 * @param string $url
 *
 * @return string
 */
function redirect(string $url)
{
    header('location: ' . $url);

    if (function_exists('fastcgi_finish_request'))
    {
        fastcgi_finish_request();

        return $url;
    }

    die;
}

/**
 * @param       $path
 * @param array $attributes
 *
 * @return string
 * @throws \Deimos\CacheHelper\Exceptions\PermissionDenied
 * @throws \Deimos\Helper\Exceptions\ExceptionEmpty
 * @throws \Deimos\Router\Exceptions\NotFound
 */
function route($path, array $attributes = [])
{
    global $builder;
    $route = $builder->router()->route($path);

    return \Deimos\Router\route($route, $attributes);
}

/**
 * @param       $path
 * @param array $attributes
 *
 * @return string
 * @throws \Deimos\CacheHelper\Exceptions\PermissionDenied
 * @throws \Deimos\Helper\Exceptions\ExceptionEmpty
 * @throws \Deimos\Router\Exceptions\NotFound
 */
function redirectRoute($path, array $attributes = [])
{
    return redirect(route($path, $attributes));
}

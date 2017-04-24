<?php

namespace Deimos\WebSocket\Controller;

/**
 * @return \Deimos\Auth\Auth
 */
function auth()
{
    global $builder;

    return $builder->auth();
}

/**
 * @param string $value
 *
 * @return \Deimos\Auth\Provider\Provider
 */
function domain($value = 'default')
{
    return auth()->domain($value);
}

/**
 * @param        $name
 * @param string $domain
 *
 * @return \Deimos\Auth\Provider\Type
 */
function provider($name, $domain = 'default')
{
    static $providers = [];

    if (empty($providers[$domain][$name]))
    {
        $providers[$domain][$name] = domain($domain)->provider($name);
    }

    return $providers[$domain][$name];
}

/**
 * @param string $password
 * @param string $domain
 *
 * @return string
 */
function passwordHash($password, $domain = 'default')
{
    return provider('domainPassword', $domain)
        ->hash($password);
}

function user($domain = 'default')
{
    return domain($domain)->user();
}

/**
 * @param string $login
 * @param string $password
 * @param string $domain
 *
 * @return mixed
 */
function login($login, $password, $domain = 'default')
{
    $provider = provider('domainPassword', $domain);

    return $provider->login($login, $password);
}

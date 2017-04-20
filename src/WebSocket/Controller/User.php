<?php

namespace Deimos\WebSocket\Controller;

use Deimos\Cookie\Cookie;
use Deimos\WebSocket\Controller;

class User extends Controller
{

    protected function exists($login)
    {
        return $this->builder()->orm()->repository('user')
                ->where('login', $login)
                ->whereOr('email', $login)
                ->count() > 0;
    }

    protected function actionLogin()
    {
        $login    = $this->request()->dataRequired('login');
        $password = $this->request()->dataRequired('password');

        if (!login($login, $password) && !$this->exists($login))
        {
            $user = $this->builder()->orm()->create('user', [
                'login'    => $login,
                'password' => passwordHash($password),
                'token'    => $this->helper()->str()->random()
            ]);

            domain()->setUser($user);
        }
        else
        {
            $user = user();
        }

        if ($user)
        {
            provider('domainSession')->persist();

            $this->builder()->cookie()->set('token', $user->id . '-' . $user->token, [
                \Deimos\Cookie\Cookie::OPTION_DOMAIN => $this->builder()->config()->get('cookie:domain')
            ]);
        }

        return redirectRoute('fpm.general');
    }

    protected function actionProfile()
    {
        $user = user();

        if (!$user)
        {
            throw new \InvalidArgumentException('User not found');
        }

        $modify   = false;
        $email    = $this->request()->data('email');
        $password = $this->request()->data('login');

        if (!empty($email))
        {
            $modify      = true;
            $user->email = $email;
        }

        if (!empty($password))
        {
            $modify   = true;
            $password = passwordHash($password);

            $user->password = $password;
        }

        if ($modify)
        {
            $user->save();
        }

        return redirectRoute('fpm.general');
    }

    /**
     * user logOut
     */
    protected function actionLogout()
    {
        $user = user();

        if ($user)
        {
            $cookie = $this->builder()->cookie();
            $cookie->set('token', null, [
                Cookie::OPTION_DOMAIN => $this->builder()->config()->get('cookie:domain'),
                Cookie::OPTION_EXPIRE => 0
            ]);
            provider('domainSession')->forget();
        }

        return redirectRoute('fpm.general');
    }

}

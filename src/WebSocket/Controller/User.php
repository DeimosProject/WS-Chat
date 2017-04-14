<?php

namespace Deimos\WebSocket\Controller;

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

            $this->builder()->cookie()->set('token', $user->token, [
                \Deimos\Cookie\Cookie::OPTION_DOMAIN =>
                    $this->builder()->config()->get('cookie:domain')
            ]);
        }

        return redirectRoute('fpm.general');
    }

    protected function actionProfile()
    {
        $user = user();

        $request = $this->request();
        if (
            $request->isPost() &&
            trim($request->urlPath(), '/') === 'save-config' &&
            $user
        )
        {
            $email    = $request->data('email');
            $password = $request->data('login');

            if (!empty($email))
            {
                $user->email = $email;
            }

            if (!empty($password))
            {
                $password = passwordHash($password);

                $user->password = $password;
            }

            count($user->getModify()) && $user->save();

            echo $request->json([
                'success' => 'ok'
            ]);
        }
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
            $cookie->remove('token');
            provider('domainSession')->forget();
        }

        return redirectRoute('fpm.general');
    }

}

<?php

namespace Deimos\WebSocket\Controller;

use Deimos\WebSocket\Controller;

class User extends Controller
{

    protected function actionLogin()
    {

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

<?php

namespace Deimos\WS;

use Deimos\Auth\Auth;
use Deimos\Auth\Provider\Type\Password;
use Deimos\Config\Config;
use Deimos\ORM\ORM;
use Deimos\Secure\Secure;

class User
{
    /**
     * @var ORM
     */
    protected $orm;
    /**
     * @var ORM
     */
    protected $user;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var Auth
     */
    protected $auth;
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct()
    {
        $this->builder = \Deimos\WS\ObjectsCache::$storage['builder'];

        $this->config = $this->builder->config();

        $this->orm = $this->builder->orm();

        $this->auth = new Auth($this->orm, $this->config->get('auth'));
        $this->orm->register('user', Models\User::class);
    }

    public function auth($login, $pass)
    {
        /**
         * @var $provider Password
         */
        $provider = $this->auth->domain()->provider('domainPassword');
        $user = $provider->login($login, $pass);

        if(!$user && !$this->orm->repository('user')->where('login', $login)->findOne())
        {
            $this->orm->create('user', [
                'email'    => $login . '@deimos',
                'login'    => $login,
                'password' => $provider->hash($pass)
            ]);

            $this->auth->domain()->setUser($user);
        }

//        $this->auth->domain()->provider('domainCookie')->persist();
        $this->auth->domain()->provider('domainSession')->persist();

        if($user && $user->id)
        {
            $token = $user->webSocketCookie;
            if(empty($user->webSocketCookie))
            {
                $token  = $user->id . '-' . \random_int(100000, 999999);
                $user->webSocketCookie = $token;
                $user->save();
            }

            $this->builder->cookie()->set('wsToken', $token);
        }

        header('Location: /');
        die;
    }

    public function user()
    {
        $user = $this->auth->domain()->user();
        return $user ?? $this->checkUser();
    }

    protected function checkUser()
    {
        $user = $this->auth->domain()->user();

        if(!$user && isset($_POST['login'], $_POST['password']))
        {
            $user = $this->auth($_POST['login'], $_POST['password']);
        }

        $this->user = $user;
        return $this->user;
    }

    /**
     * @return int[]
     */
    public function chatId()
    {
        $this->checkUser();

//        if($this->user)
//        {
//            $this->orm->register('usersChatId', Models\UsersChatId::class);
//
//            return $this->orm->repository('usersChatId')
//                ->select('id')
//                ->where('userId', $this->user->id)
//                ->find(false);
//        }

        return [];
    }

    public function logout()
    {
        $request = $this->builder->request();
        if(trim($request->urlPath(), '/') === 'logout')
        {
            $this->builder->cookie()->remove('wsToken');
            $this->auth->domain()->provider('domainSession')->forget();
            header('Location: /');
            die;
        }
    }

    public function saveConfig()
    {
        $request = $this->builder->request();
        if(
            $request->isPost() &&
            trim($request->urlPath(), '/') === 'save-config' &&
            $this->user()
        )
        {
            $user = $this->user();
            $email = $request->data('email');
            $password = $request->data('login');

            if(!empty($email))
            {
                $user->email = $email;
            }

            if(!empty($password))
            {
                $provider = $this->auth->domain()->provider('domainPassword');
                $password = $provider->hash($password);

                $user->password = $password;
            }

            count($user->getModify()) && $user->save();

            echo $request->json([
                'success' => 'ok'
            ]);
            die;
        }
    }

}

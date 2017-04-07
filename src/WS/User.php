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
            $secure = new Secure();
            $token = $secure->encrypt($user->id);

            $this->builder->cookie()->set('wsToken', $token);
        }

        return $user;
    }

    public function user()
    {
        return $this->auth->domain()->user();
    }

    protected function checkUser()
    {
        $user = $this->user();

        if(!$user && isset($_POST['login'], $_POST['password']))
        {
            $user = $this->auth($_POST['login'], $_POST['password']);
        }

        $this->user = $user;
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

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->user ? $this->user->login : '';
    }

}

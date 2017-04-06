<?php

namespace Deimos\WS;

class Builder extends \Deimos\Builder\Builder
{

    /**
     * @return \Deimos\ORM\ORM
     */
    public function orm()
    {
        return $this->once(function ()
        {
            $database = new \Deimos\Database\Database($this->config()->get('db'));

            return new \Deimos\ORM\ORM($this, $database);
        }, __METHOD__);
    }

    /**
     * @return \Deimos\Auth\Auth
     */
    public function auth()
    {
        return $this->once(function ()
        {
            $this->orm()->register('user', Models\User::class);

            return new \Deimos\Auth\Auth($this->orm(), $this->config()->get('auth'));
        }, __METHOD__);
    }

    /**
     * @return \Deimos\Config\Config
     */
    public function config()
    {
        return $this->once(function ()
        {
            return new \Deimos\Config\Config(ROOT_DIR . '/assets/config', $this);
        }, __METHOD__);
    }

    /**
     * @return \Deimos\Cookie\Cookie
     */
    public function cookie()
    {
        return $this->once(function ()
        {
            return new \Deimos\Cookie\Cookie($this);
        }, __METHOD__);
    }

}

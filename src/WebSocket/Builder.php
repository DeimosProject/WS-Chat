<?php

namespace Deimos\WebSocket;

use Deimos\Auth\Auth;
use Deimos\CacheHelper\SliceHelper;
use Deimos\Config\Config;
use Deimos\Cookie\Cookie;
use Deimos\Database\Database;
use Deimos\Flow\Configure;
use Deimos\Flow\Flow;
use Deimos\Helper\Helper;
use Deimos\Migrate\Migrate;
use Deimos\ORM\ORM;
use Deimos\Router\Router;

class Builder extends \Deimos\Builder\Builder
{

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * Builder constructor.
     *
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/\\') . '/';
    }

    /**
     * @return string
     */
    public function path($path)
    {
        return $this->rootDir . $path;
    }

    /**
     * @return Database
     */
    public function database()
    {
        return $this->once(function ()
        {
            $slice = $this->config()->get('db');

            return new Database($slice);
        }, __METHOD__);
    }

    /**
     * @return ORM
     */
    public function orm()
    {
        return $this->once(function ()
        {
            $orm = new ORM(
                $this->helper(),
                $this->database()
            );

            $slice = $this->config()->get('relationships');

            $orm->setConfig($slice->asArray());

            return $orm;
        }, __METHOD__);
    }

    /**
     * @return Auth
     */
    public function auth()
    {
        return $this->once(function ()
        {
            $slice = $this->config()->get('auth');

            return new Auth($this->orm(), $slice);
        }, __METHOD__);
    }

    /**
     * @return Config
     */
    public function config()
    {
        return $this->once(function ()
        {
            return new Config(
                $this->helper(),
                $this->path('assets/config')
            );
        }, __METHOD__);
    }

    /**
     * @return Cookie
     */
    public function cookie()
    {
        return $this->once(function ()
        {
            return new Cookie($this);
        }, __METHOD__);
    }

    /**
     * @return Helper
     */
    public function helper()
    {
        return $this->once(function ()
        {
            return new Helper();
        }, __METHOD__);
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->once(function ()
        {
            $request = new Request($this->helper());
            $request->setRouter($this->router());

            return $request;
        }, __METHOD__);
    }

    /**
     * @return SliceHelper
     */
    protected function sliceHelper()
    {
        return $this->once(function ()
        {
            return new SliceHelper($this->path('assets/cache'));
        }, __METHOD__);
    }

    /**
     * @return Router
     */
    public function router()
    {
        return $this->once(function ()
        {
            $slice = $this->config()->get('resolver');

            return new Router($slice, $this->sliceHelper());
        }, __METHOD__);
    }

    /**
     * @return Migrate
     */
    public function migrate()
    {
        return $this->once(function ()
        {
            $migrate = new Migrate($this->orm());
            $migrate->setPath($this->path('assets/migrations'));

            return $migrate;
        }, __METHOD__);
    }

    /**
     * @return Flow
     */
    public function flow()
    {
        return $this->once(function ()
        {

            $slice = $this->config()->get('flow');

            $configure = new Configure();

            if ($slice->getData('debug'))
            {
                $configure->debugEnable();
            }

            if ($slice->getData('php'))
            {
                $configure->phpEnable();
            }

            $defaultConfig = new \Deimos\Flow\DefaultConfig();
            $di            = new DI($this, $defaultConfig);
            $configure->di($di);

            $flow = (new Flow($configure))
                ->setCompileDir($this->path($slice->getData('compile', 'assets/cache')))
                ->setTemplateDir($this->path($slice->getData('view', 'assets/view')));

            foreach ($slice->getData('assign', []) as $name => $value)
            {
                $flow->assign($name, $value);
            }

            return $flow;

        }, __METHOD__);
    }

}

<?php

namespace Deimos\WebSocket;

use Deimos\Flow\DefaultConfig;
use function Deimos\WebSocket\Controller\route;

class DI extends \Deimos\Flow\DefaultContainer
{

    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder, DefaultConfig $config = null)
    {
        parent::__construct($config, $builder->helper());

        $this->builder = $builder;
    }

    protected function configure()
    {
        parent::configure();

        $this->callback('asset', function ($path)
        {
            $realPath = $this->builder->path('web' . $path);

            if ($realPath)
            {
                return
                    $path .
                    (false === strpos($path, '?') ? '?' : '&') .
                    filemtime($realPath);
            }

            return $path;
        });

        $this->callback('route', function ($path, array $attributes = [])
        {
            return route('fpm.' . $path, $attributes);
        });
    }

}

<?php

namespace Deimos\WebSocket;

use Deimos\Flow\DefaultConfig;
use Deimos\Router;

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

        $this->callback('route', function ($path, array $attributes = [])
        {
            $route = $this->builder
                ->router()
                ->route('fpm.' . $path);

            return Router\route($route, $attributes);
        });
    }

}

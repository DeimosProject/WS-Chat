<?php

namespace Deimos\WebSocket;

use Deimos\Request\Request;

class Controller extends \Deimos\Controller\Controller
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @return \Deimos\WebSocket\Builder
     */
    protected function builder()
    {
        return $this->builder;
    }

    protected function configure()
    {
    }

    protected function before()
    {
        // TODO: Implement before() method.
    }

    protected function after($data)
    {
        // TODO: Implement after() method.
    }

}
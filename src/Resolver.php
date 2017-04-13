<?php

namespace Deimos;

use Deimos\Controller\Processor;

class Resolver extends Processor
{

    protected $attribute = 'app';

    /**
     * @return Console\App
     */
    protected function buildConsole()
    {
        return new Console\App($this->builder);
    }

    /**
     * @return WebSocket\App
     */
    protected function buildWebSocket()
    {
        return new WebSocket\App($this->builder);
    }

}

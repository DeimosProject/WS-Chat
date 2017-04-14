<?php

namespace Deimos\WebSocket;

use Deimos\Controller\Processor;

class App extends Processor
{

    /**
     * @return Controller\General
     */
    protected function buildGeneral()
    {
        return new Controller\General($this->builder);
    }

}

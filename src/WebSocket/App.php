<?php

namespace Deimos\WebSocket;

use Deimos\Controller\Processor;

class App extends Processor
{

    /**
     * @return Controller\Chat
     */
    protected function buildChat()
    {
        return new Controller\Chat($this->builder);
    }

}

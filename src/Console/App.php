<?php

namespace Deimos\Console;

use Deimos\Controller\Processor;

class App extends Processor
{

    /**
     * @return Controller\Migrate
     */
    protected function buildMigrate()
    {
        return new Controller\Migrate($this->builder);
    }

    /**
     * @return Controller\User
     */
    protected function buildUser()
    {
        return new Controller\User($this->builder);
    }

    /**
     * @return Controller\Daemon
     */
    protected function buildDaemon()
    {
        return new Controller\Daemon($this->builder);
    }

    /**
     * @return Controller\Help
     */
    protected function buildHelp()
    {
        return new Controller\Help($this->builder);
    }

}

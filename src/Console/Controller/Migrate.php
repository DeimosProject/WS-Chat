<?php

namespace Deimos\Console\Controller;

use Deimos\WebSocket\Controller;

class Migrate extends Controller
{

    /**
     * Fascilitates updating your database structure by letting you keep track of
     * adding and removing columns, changing field types and table modifications.
     *
     * You can specify everything using arrays,
     * so there is no need to write any SQL at all.
     *
     * @return string
     */
    public function actionDefault()
    {
        /**
         * @var $migrate \Deimos\Migrate\Migrate
         */
        $migrate = $this->builder()->migrate();

        return implode(PHP_EOL, $migrate->run());
    }

}

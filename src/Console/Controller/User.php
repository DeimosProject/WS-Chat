<?php

namespace Deimos\Console\Controller;

use Deimos\ORM\ORM;
use Deimos\WebSocket\Controller;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

class User extends Controller
{

    protected function actionChangePassword()
    {
        throw new \InvalidArgumentException(__METHOD__);
    }

    protected function actionList()
    {
        /**
         * @var $orm ORM
         */
        $buffer = new BufferedOutput();
        $table  = new Table($buffer);
        $orm    = $this->builder()->orm();

        $users = $orm->repository('user')
            ->limit(50)
            ->find(false);

        if (!empty($users))
        {
            $table->setHeaders(array_keys($users[0]));
        }

        $table->setRows($users);
        $table->render();

        return $buffer->fetch();
    }

}

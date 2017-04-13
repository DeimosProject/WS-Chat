<?php

namespace Deimos\WebSocket\Controller;

use Deimos\WebSocket\Controller;

class Chat extends Controller
{

    protected function actionDefault()
    {
        $flow = $this->builder()->flow();
        $user = new \Deimos\WebSocket\User($this->builder());

        $user->saveConfig();
        $user->logout();

        $flow->assign('user', $user);

        return $flow->render('layout');
    }

}

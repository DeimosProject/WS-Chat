<?php

namespace Deimos\WebSocket\Controller;

use Deimos\WebSocket\Controller;

class General extends Controller
{

    protected function actionDefault()
    {
//        $message = '@hello-world@rezident3@sttv@m.babichev yandex JavaScript';
//
//        preg_match_all('~@(?<login>[\w-.]+)~', $message, $matches);
//        $message = preg_replace('~@[\w-.]+\s{1}~', '', $message);
//        var_dump($matches, $message);die;

        $flow = $this->builder()->flow();

        return $flow->render('layout');
    }

}

<?php

namespace Deimos\WebSocket;

class Request extends \Deimos\Request\Request
{

    public function sendJson(array $data = array(), $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        die($this->json($data, $options));
    }

}

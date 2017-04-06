<?php

namespace Deimos\WS;

use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;

class WS
{
    protected $server;

    public function run($port = 8080, $address = '0.0.0.0')
    {
        $ws = new WsServer(new Chat);
        $ws->disableVersion(0); // old, bad, protocol version

        // Make sure you're running this as root
        $this->server = IoServer::factory(new HttpServer($ws), $port, $address);
        $this->server->run();
    }
}

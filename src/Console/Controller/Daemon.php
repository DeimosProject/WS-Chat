<?php

namespace Deimos\Console\Controller;

use Deimos\WebSocket\Controller;
use Deimos\WebSocket\Server\Application;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Daemon extends Controller
{

    /**
     * Used to start the server daemon
     *
     * @throws \Deimos\Config\Exceptions\PermissionDenied
     * @throws \Deimos\Helper\Exceptions\ExceptionEmpty
     */
    protected function actionDefault()
    {
        $config = $this->builder()->config();
        $slice  = $config->get('webSocket');

        $host = $slice->getData('server.host', '0.0.0.0');
        $port = $slice->getData('server.port', '8080');

        $webSocket = new WsServer(new Application($this->builder()));
        $webSocket->disableVersion(0); // old, bad, protocol version

        // Make sure you're running this as root
        $server = IoServer::factory(new HttpServer($webSocket), $port, $host);
        $server->run();
    }

}

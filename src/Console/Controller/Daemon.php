<?php

namespace Deimos\Console\Controller;

use Deimos\WebSocket\Controller;
use Deimos\WebSocket\Server\Application;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Daemon extends Controller
{

    protected $path = 'assets/cache/daemon.pId';

    protected function pId()
    {
        $file = $this->builder()->path($this->path);

        if (is_file($file))
        {
            $pid = file_get_contents($file);

            if (posix_kill($pid, 0))
            {
                return $pid;
            }

            if (!unlink($file))
            {
                exit(-1);
            }
        }

        return false;
    }

    /**
     * Used to start the server daemon
     *
     * @throws \Deimos\Config\Exceptions\PermissionDenied
     * @throws \Deimos\Helper\Exceptions\ExceptionEmpty
     */
    protected function actionStart()
    {
        !$this->pId() OR exit('The daemon is started' . PHP_EOL);

        ob_start();
        $pId = pcntl_fork();
        !$pId OR exit(0);

        fclose(STDIN);

        file_put_contents($this->builder()->path($this->path), getmypid());

        $config = $this->builder()->config();
        $slice  = $config->get('webSocket');

        $host = $slice->getData('server.host', '0.0.0.0');
        $port = $slice->getData('server.port', '8080');

        $webSocket = new WsServer(new Application($this->builder()));
        $webSocket->disableVersion(0); // old, bad, protocol version

        try
        {
            // Make sure you're running this as root
            $server = IoServer::factory(new HttpServer($webSocket), $port, $host);
            $server->run();
        }
        catch (\Throwable $exception)
        {
            unlink($this->builder()->path($this->path));
            ob_clean();

            posix_kill(getmygid(), SIGTERM);
            exit('The daemon is started. Kill him!' . PHP_EOL);
        }

        fclose(STDOUT);
        fclose(STDERR);

        ob_clean();

        return 'Started background daemon with PID ' . $pId . PHP_EOL;
    }

    /**
     * Used to stop the server daemon
     */
    protected function actionStop()
    {
        $pId = $this->pId();

        if ($pId)
        {
            posix_kill($pId, SIGTERM);
            exit('The daemon `' . $pId . '` is killed' . PHP_EOL);
        }

        exit('The daemon not found' . PHP_EOL);
    }

}

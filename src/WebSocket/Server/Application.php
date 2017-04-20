<?php

namespace Deimos\WebSocket\Server;

use Deimos\ORM\Entity;
use Deimos\ORM\Queries\Query;
use Deimos\Paginate\Paginate;
use Deimos\Secure\Secure;
use Deimos\WebSocket\Builder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use function Deimos\WebSocket\Controller\user;

class Application implements MessageComponentInterface
{

    /**
     * @var SplObjectStorage
     */
    protected $connections;

    /**
     * @var array
     */
    protected $users;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Deimos\ORM\ORM
     */
    protected $orm;

    /**
     * Chat constructor.
     */
    public function __construct(Builder $builder)
    {
        $this->builder     = $builder;
        $this->orm         = $builder->orm();
        $this->connections = new SplObjectStorage();
        $this->users       = [];

        $path    = $builder->path('daemon.log');
        $handler = new StreamHandler($path);

        $this->logger = new Logger('daemon');
        $this->logger->pushHandler($handler);
    }

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @param  ConnectionInterface $connection The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $connection)
    {
        $this->logger->addInfo('Connection `' . $this->resourceIdFrom($connection) . '` is established.');

        /**
         * @var $connection \Ratchet\WebSocket\Version\RFC6455\Connection
         */
        $user = $this->userFrom($connection);

        /**
         * @var $connection \Ratchet\WebSocket\Version\RFC6455\Connection
         */
        if (empty($user->id))
        {
            return $this->send(
                $connection,
                $this->blob('For a start log in.', Types::INFO),
                [$connection]
            );
        }

        /**
         * @var $chatQuery Query
         */
        $chatQuery = $this->orm->repository('chat')
            ->select('login', 'text', 'createdAt')
            ->join(['u' => 'users'])
            ->on('chat.userId', 'u.id')
            ->orderBy('createdAt', 'DESC');

        $pager = new Paginate();
        $pager->queryPager($chatQuery);

        $this->send(
            $connection,
            $this->blob($pager->currentItems(false), Types::ANY),
            [$connection]
        );

        $this->connections->attach($connection, $user);
        $this->users[$user->id][$this->resourceIdFrom($connection)] = $connection;

        $this->onlineList($connection);

        return null;
    }

    /**
     * отправляет пользователям обновленный список "онлайн"
     */
    protected function onlineList(ConnectionInterface $connection)
    {
        $this->send(
            $connection,
            $this->blob($this->connections->asArray(), Types::USER_LIST)
        );
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $connection will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $connection The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $connection)
    {
        $user = $this->userFrom($connection);

        if($user)
        {
            $this->connections->detach($connection);
            unset($this->users[$user->id()][$this->resourceIdFrom($connection)]);

            $this->onlineList($connection);
        }

        $this->logger->addInfo('Connection `' . $this->resourceIdFrom($connection) . '` has disconnected');
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param  ConnectionInterface $connection
     * @param  \Exception          $e
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        $this->logger->addError($e->getMessage());

        $connection->close();
    }

    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface $connection The socket/connection that sent the message to your application
     * @param  string                       $message    The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        $message = json_decode($message);

        $user = user();

        $this->builder->orm()->create('chat', [
            'userId' => $user->id,
            'text'   => $message->text
        ]);

        $this->send($connection, $this->blob($message->text));
    }

    /**
     * @param ConnectionInterface    $connection
     * @param array                  $blob
     * @param SplObjectStorage|array $connections
     */
    protected function send(ConnectionInterface $connection, array $blob, $connections = null)
    {
        $connections = $connections ?: $this->connections;

        /**
         * @var ConnectionInterface $client
         */
        foreach ($connections as $client)
        {
            $blob['own'] = $client === $connection;

            $client->send($this->json($blob));
        }
    }

    /**
     * @param string $message
     * @param int    $type
     *
     * @return string
     */
    protected function message($message, $type = Types::MESSAGE)
    {
        return $this->json($this->blob($message, $type));
    }

    /**
     * @param string|array        $data
     * @param int                 $type
     * @param ConnectionInterface $connection
     *
     * @return array
     */
    protected function blob($data, $type = Types::MESSAGE, ConnectionInterface $connection = null)
    {
        return Types::blob([
            'connections' => $this->connections,
            'connection'  => $connection,
            'data'        => is_array($data) ? $data : null,
            'message'     => is_array($data) ? null : $data,
            'type'        => $type,
        ]);
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function json(array $data)
    {
        return $this->builder->helper()->json()->encode($data);
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return string
     */
    protected function resourceIdFrom(ConnectionInterface $connection)
    {
        return $connection->resourceId;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return \Guzzle\Http\Message\EntityEnclosingRequest
     */
    protected function connectionRequest(ConnectionInterface $connection)
    {
        return $connection->WebSocket->request;
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return Entity|null
     */
    protected function userFrom(ConnectionInterface $connection)
    {
        $tokenCookie = urldecode($this->connectionRequest($connection)->getCookie('token'));
        $token       = (new Secure())->decrypt($tokenCookie);
        $token       = explode('-', $token, 2);

        if(empty($token[1]))
        {
            return null;
        }

        return $this->orm->repository('user')
            ->where('id', (int)$token[0])
            ->where('token', $token[1])
            ->findOne();
    }

}

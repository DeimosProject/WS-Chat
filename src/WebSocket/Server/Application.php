<?php

namespace Deimos\WebSocket\Server;

use Deimos\Secure\Secure;
use Deimos\WebSocket\Builder;
use Deimos\WebSocket\Models;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Application implements MessageComponentInterface
{
    const STATUS_OK      = 'ok';
    const STATUS_ERROR   = 'error';
    const STATUS_INFO    = 'info';
    const STATUS_WARNING = 'warning';

    const DATA_TYPE_MESSAGE = 'message';
    const DATA_TYPE_USERS   = 'users';
    const DATA_TYPE_SETUP   = 'setup';

    /**
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * @var array
     */
    protected $users;

    /**
     * @var \Deimos\ORM\ORM
     */
    protected $orm;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Chat constructor.
     */
    public function __construct(Builder $builder)
    {
        $this->builder     = $builder;
        $this->orm         = $builder->orm();
        $this->connections = new \SplObjectStorage();
        $this->users       = [];
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
        $request = $connection->WebSocket->request;
        /**
         * @var $connection \Ratchet\WebSocket\Version\RFC6455\Connection
         */
        $user = $this->getUser($request);

        /**
         * @var $connection \Ratchet\WebSocket\Version\RFC6455\Connection
         */
        if (empty($user->id))
        {
            $connection->send($this->message('<h2>Сперва залогиньтесь.</h2>'));

            return;
        }

        $connection->send($this->message('', self::STATUS_OK, [
            'type'     => self::DATA_TYPE_SETUP,
            'messages' => $this->builder->orm()
                ->repository('chat')
                ->join(['u' => 'users'])
                ->on('chat.userId', 'u.id')
                ->select('text', 'time', 'login')
                ->orderBy('time', 'DESC')
                ->limit(50)
                ->find(false)
        ]));

        $this->connections->attach($connection, $user);
        $this->users[$user->id][$connection->resourceId] = $connection;

        $this->renewUsers();
    }

    /**
     * отправляет пользователям обновленный список "онлайн"
     */
    protected function renewUsers()
    {
        $users = [];
        foreach ($this->connections as $connection)
        {
            $user = $this->connections[$connection];

            $users[$user->id] = [
                'id'     => $user->id,
                'login'  => $user->login,
                'avatar' => $user->avatar(),
            ];
        }

        $message = $this->message('', self::STATUS_OK, [
            'type' => self::DATA_TYPE_USERS,
            'users' => $users,
        ]);

        foreach ($this->connections as $client)
        {
            $client->send($message);
        }
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
        $user = $this->getUser($connection->WebSocket->request);
        echo __FUNCTION__ . PHP_EOL;

        if($user)
        {
            $this->connections->detach($connection);

            $this->renewUsers();
        }

        echo "Connection {$connection->resourceId} has disconnected\n";
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
        echo __FUNCTION__ . PHP_EOL;
        echo "An error has occurred: {$e->getMessage()}\n";

        var_dump($e);

        $connection->close();
    }

    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface $connection The socket/connection that sent the message to your application
     * @param  string                       $msg        The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $connection, $msg)
    {
        $request = $connection->WebSocket->request;

        if (!$this->checkTime($connection))
        {
            return;
        }

        $msg = json_decode($msg);

        if (json_last_error() || empty($msg->text) || mb_strlen($msg->text) > 254)
        {
            return;
        }

        if (!empty($msg->to) && empty($this->users[$msg->to])) // если тот, кому послали не онлайн
        {
            return;
        }

        $user = $this->getUser($request);

        if(!$user)
        {
            return;
        }

        $login = '';
        if (!empty($msg->to))
        {
            $conn   = current($this->users[$msg->to]);
            $userTo = $this->connections[$conn];

            $login = '<b>&lap; ' . $user->login . ' > ' . $userTo->login . ' &gap;</b> ';
        }
        else
        {
            $login = '<b>&lap; ' . $user->login . ' &gap;</b> ';
        }

        $message = $login . '<i>' . date('Y-m-d H:i:s') . '</i>' . htmlspecialchars($msg->text);

        if (!empty($msg->to))
        {
            if ($msg->to === $user->id)
            {
                current($this->users[$msg->to])->send($this->message('Нельзя отправить сообщение самому себе', self::STATUS_ERROR));

                return;
            }

            foreach ($this->users[$msg->to] as $conn)
            {
                $conn->send($this->message($message, self::STATUS_OK, ['class' => 'private']));
            }

            foreach ($this->users[$user->id] as $conn)
            {
                $conn->send($this->message($message, self::STATUS_OK, ['class' => 'private-own']));
            }

            return;
        }

        $this->builder->orm()->create('chat', [
            'userId' => $user->id,
            'text'   => $msg->text,
            'time'   => date('Y-m-d H:i:s')
        ]);

        foreach ($this->connections as $client)
        {
            $class = [];

            if ($connection === $client)
            {
                $class = ['class' => 'own'];
            }

            $client->send($this->message($message, self::STATUS_OK, $class));
        }
    }

    /**
     * @param $request \Guzzle\Http\Message\EntityEnclosingRequest
     *
     * @return Models\User
     */
    protected function getUser($request)
    {
        $tokenCookie = urldecode($request->getCookie('token'));
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

    /**
     * @param  \Ratchet\ConnectionInterface $connection The socket/connection that sent the message to your application
     *
     * @return bool
     */
    protected function checkTime(&$connection)
    {
        $currentTime = time();
        if (!isset($connection->time) || (($connection->time + 1) < $currentTime))
        {
            $connection->spam = 0;
            $connection->time = $currentTime;
            return true;
        }

        if (!empty($connection->spam) && $connection->spam < 2)
        {
            usleep(500000);
            $connection->send($this->message('Спам не приветствуется. Подождите немного', self::STATUS_WARNING));
            $connection->spam = 2;
        }
        else if (empty($connection->spam))
        {
            $connection->spam = 1;
            return true;
        }

        return false;
    }

    protected function message($text, $status = self::STATUS_OK, $moreData = [])
    {
        return json_encode(array_merge([
            'text' => $text,
            'status' => $status,
            'type' => self::DATA_TYPE_MESSAGE
        ], $moreData));
    }

}

<?php

namespace Deimos\WS;

use Deimos\Secure\Secure;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class Chat implements MessageComponentInterface
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
    protected $clients;

    /**
     * <b>$this->users[$user->id][$conn->resourceId] = [$conn, $user];</b>
     * @var array
     */
    protected $users = [];

    /**
     * @var \Deimos\ORM\ORM
     */
    protected $orm;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param string $message
     * @param string $log
     */
    protected function writeLog($message, $log = 'messages.log')
    {
        $filename = ROOT_DIR . '/log/' . $log;

        is_file($filename) || touch($filename) || die('log write error!');

        file_put_contents($filename, date('Y-m-d H:i:s') . PHP_EOL . $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Chat constructor.
     */
    public function __construct()
    {
        $this->builder = \Deimos\WS\ObjectsCache::$storage['builder'];

        $this->clients = new \SplObjectStorage();

        $this->orm = $this->builder->orm();
    }

    /**
     * When a new connection is opened it will be passed to this method
     *
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $request = $conn->WebSocket->request;
        /**
         * @var $conn \Ratchet\WebSocket\Version\RFC6455\Connection
         */
        $user = $this->getUser($request);
        echo __FUNCTION__ . PHP_EOL;

        if (empty($user->id))
        {
            $conn->send($this->message('<h2>Сперва залогиньтесь.</h2>'));

            return;
        }

        $conn->send($this->message('', self::STATUS_OK, [
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

        $this->users[$user->id][$conn->resourceId] = [$conn, $user];

        $this->clients->attach($conn);

        $this->renewUsers();
    }

    /**
     * отправляет пользователям обновленный список "онлайн"
     */
    protected function renewUsers()
    {
        $users = [];
        foreach ($this->users as $user) {
            $u = current($user);

            if(isset($u[1]))
            {
                $users[] = [
                    'id'    => $u[1]->id,
                    'login' => $u[1]->login,
                    'image' => md5('' . $u[1]->email),
                ];
            }
        }

        $message = $this->message('', self::STATUS_OK, [
            'type' => self::DATA_TYPE_USERS,
            'users' => $users,
        ]);

        foreach ($this->clients as $client)
        {
            $client->send($message);
        }
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        $user = $this->getUser($conn->WebSocket->request);
        echo __FUNCTION__ . PHP_EOL;

        if($user)
        {
            unset($this->users[$user->id][$conn->resourceId]);
            $this->clients->detach($conn);

            $this->renewUsers();

            if(empty($this->users[$user->id])) {

                $user->webSocketCookie = '';
                $user->save();
            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     *
     * @param  ConnectionInterface $conn
     * @param  \Exception          $e
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo __FUNCTION__ . PHP_EOL;
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * Triggered when a client sends data through the socket
     *
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     * @param  string                       $msg  The message received
     *
     * @throws \Exception
     */
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        $request = $conn->WebSocket->request;

        if (!$this->checkTime($conn))
        {
            return;
        }

        $msg = json_decode($msg);

        if (json_last_error() || empty($msg->text) || mb_strlen($msg->text) > 254)
        {
            return;
        }

        if (!empty($msg->to))
        {
            if (empty($this->users[$msg->to])) // если тот, кому послали не онлайн
            {
                return;
            }
        }

        $user = $this->getUser($request);

        if(!$user)
        {
            return;
        }

        $login = '';
        if (!empty($msg->to))
        {
            $login = '<b>&lap; ' . $user->login . ' > ' . $this->users[$msg->to]->login . ' &gap;</b> ';
        }
        else
        {
            $login = '<b>&lap; ' . $user->login . ' &gap;</b> ';
        }

        $message = $login . '<i>' . date('Y-m-d H:i:s') . '</i>' . htmlspecialchars($msg->text);

        if (!empty($msg->to))
        {
            if($msg->to == $user->id)
            {
                $this->clients[$msg->to]->send($this->message('Нельзя отправить сообщение самому себе', self::STATUS_ERROR));

                return;
            }

            foreach ($this->users[$msg->to] as $connection) {
                $connection[0]->send($this->message($message, self::STATUS_OK, ['class' => 'private']));
            }
            foreach ($this->users[$msg->id] as $connection)
            {
                $connection[0]->send($this->message($message, self::STATUS_OK, ['class' => 'private-own']));
            }

            return;
        }

        $this->builder->orm()
            ->create('chat', [
                'userId' => $user->id,
                'text' => $msg->text,
                'time' => date('Y-m-d H:i:s')
            ]);

        foreach ($this->clients as $client)
        {
            $class = [];
            if ($conn === $client)
            {
                $class = ['class' => 'own'];
            }

            $client->send($this->message($message, self::STATUS_OK, $class));
        }
    }

    /**
     * @param $request \Guzzle\Http\Message\EntityEnclosingRequest
     *
     * @return \Deimos\WS\Models\User
     */
    public function getUser($request)
    {
        $secure = new Secure();
        $token = $secure->decrypt($request->getCookie('wsToken'));

        list($token, $x) = explode('-', $token);

        return $this->orm->repository('user')
            ->where('id', (int)$token)
            ->findOne();
    }

    /**
     * @param  \Ratchet\ConnectionInterface $conn The socket/connection that sent the message to your application
     *
     * @return bool
     */
    protected function checkTime(&$conn)
    {
        $_ = time();
        if(!isset($conn->time) || (($conn->time + 1) < $_))
        {
            $conn->spam = 0;
            $conn->time = $_;
            return true;
        }

        if(!empty($conn->spam) && $conn->spam < 2)
        {
            usleep(500000);
            $conn->send($this->message('Спам не приветствуется. Подождите немного', self::STATUS_WARNING));
            $conn->spam = 2;
        }
        else if(empty($conn->spam))
        {
            $conn->spam = 1;
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

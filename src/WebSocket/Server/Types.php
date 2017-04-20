<?php

namespace Deimos\WebSocket\Server;

use function Deimos\WebSocket\Controller\user;

class Types
{

    const INFO      = 0;
    const MESSAGE   = 1;
    const ANY       = 2;
    const USER_LIST = 3;

    /**
     * @param array $options
     *
     * @return array
     */
    public static function blob(array $options)
    {
        if ($options['type'] === self::ANY)
        {
            return $options;
        }

        if ($options['type'] === self::USER_LIST)
        {
            return [
                'type' => $options['type'],
                'data' => $options['connections']->asArray()
            ];
        }

        return [
            'type' => $options['type'],
            'data' => [
                'message' => $options['message'],
                'user'    => $options['user'] ?? ($options['type'] === self::MESSAGE ? user()->asArray() : null)
            ]
        ];
    }

}

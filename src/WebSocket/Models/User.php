<?php

namespace Deimos\WebSocket\Models;

use Deimos\ORM\Entity;

class User extends Entity
{

    public static function generateAvatarPath($email)
    {
        return '//secure.gravatar.com/avatar/' . md5($email);
    }

    public function avatar()
    {
        return static::generateAvatarPath($this->email);
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return [
            'login'  => $this->login,
            'avatar' => $this->avatar()
        ];
    }

}

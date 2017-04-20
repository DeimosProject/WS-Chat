<?php

namespace Deimos\WebSocket\Models;

use Deimos\ORM\Entity;

class User extends Entity
{

    public function avatar()
    {
        return '//secure.gravatar.com/avatar/' . md5($this->email);
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return ['avatar' => $this->avatar()] + parent::asArray();
    }

}

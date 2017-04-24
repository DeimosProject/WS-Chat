<?php

namespace Deimos\WebSocket\Server;

class SplObjectStorage extends \SplObjectStorage
{

    /**
     * @return array
     */
    public function asArray()
    {
        $results = [];

        foreach ($this as $obj)
        {
            $user                 = $this[$obj];
            $results[$user->id()] = $user;
        }

        return array_values($results);
    }

}

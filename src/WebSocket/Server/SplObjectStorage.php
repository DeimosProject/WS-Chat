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
            $results[] = $this[$obj];
        }

        return $results;
    }

}

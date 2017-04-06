<?php

require_once __DIR__ . '/vendor/autoload.php';

define('ROOT_DIR', __DIR__);
\Deimos\WS\ObjectsCache::$storage['builder'] = new \Deimos\WS\Builder();

$ws = new \Deimos\WS\WS();

$ws->run();

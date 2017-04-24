<?php

include_once __DIR__ . '/vendor/autoload.php';

$builder  = new Deimos\WebSocket\Builder(__DIR__);
$resolver = new \Deimos\Resolver($builder);

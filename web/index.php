<?php
    define('ROOT_DIR', dirname(__DIR__));

    require_once __DIR__ . '/../vendor/autoload.php';

    \Deimos\WS\ObjectsCache::$storage['builder'] = new \Deimos\WS\Builder();

    $user = new \Deimos\WS\User();
    $ids = $user->chatId();
?><!DOCTYPE html>
<html>
<head>
    <title>Deimos chat</title>
    <link href="/css/chat.css" rel="stylesheet"/>
    <script src="//code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha256-/SIrNqv8h6QGKDuNoLGA4iret+kyesCkHGzVUUV0shc=" crossorigin="anonymous"></script>
    <script src="/js/chat.js"></script>
    <script src="/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
<div class="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <header class="login-form">
                    <span>Пользователь&nbsp;</span>
                    <?php
                    if ($user->user()) {
                    ?>
                        <span><b>&nbsp; &lap; <?php echo $user->getLogin(); ?> &gap;</b></span>
                    <?php
                    } else {
                    ?>

                    <form action="/" method="POST">
                        <span><input name="login" placeholder="user name" id="user"></span>
                        <span><input name="password" placeholder="password" id="user"></span>
                        <span><button id="ok">Ок</button></span>
                    </form>

                    <?php } ?>
                </header>
                <div>
                    <div class="body messages" id="messages">
                        <div class="inner"></div>
                    </div>
                    <div class="message-area input-group">
                        <input class="form-control" disabled="disabled" maxlength="50" id="message" name="message">
                        <span class="input-group-addon btn btn-success" id="send">Send message</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2" id="users"></div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
</body>
</html>


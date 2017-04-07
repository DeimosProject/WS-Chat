<?php
    define('ROOT_DIR', dirname(__DIR__));

    require_once __DIR__ . '/../vendor/autoload.php';

    \Deimos\WS\ObjectsCache::$storage['builder'] = new \Deimos\WS\Builder();

    $user = new \Deimos\WS\User();
    $ids = $user->chatId();
    $version = 3;
?><!DOCTYPE html>
<html>
<head>
    <title>Deimos chat</title>
    <link href="/css/chat.css?v<?=$version?>" rel="stylesheet"/>
    <link href="/bootstrap/css/bootstrap.min.css?v<?=$version?>" rel="stylesheet"/>
    <link href="/bootstrap/css/bootstrap-theme.min.css?v<?=$version?>" rel="stylesheet"/>
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
                        <span><b title="Settings" class="user-config-btn" data-toggle="modal" data-target="#user-settings-modal"> &lap; <?php echo $user->getLogin(); ?> &gap;</b></span>
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
                        <div class="form-control no-padding">
                            <input class="form-control" disabled="disabled" maxlength="50" id="message" name="message">
                            <select class="form-control" id="send-to"></select>
                        </div>
                        <span class="input-group-addon btn btn-success" id="send">Send message</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2" id="users">
                <div class="row">
                    <div class="header">Пользователи онлайн:</div>
                    <div class="list"></div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div id="user-settings-modal" tabindex="-1" role="dialog" class="modal fade">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-header-text">User settings</h4>
                </div>
                <div class="modal-body">
                    <div id="user-config-modal-wrapper">
                        <div class="input-group">
                            <input class="form-control" name="email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
</div>
<script src="//code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha256-/SIrNqv8h6QGKDuNoLGA4iret+kyesCkHGzVUUV0shc=" crossorigin="anonymous"></script>
<script src="/js/chat.js?v<?=$version?>"></script>
<script src="/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>

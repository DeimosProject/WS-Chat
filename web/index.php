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
    <div class="outer">
        <div class="inner">
            <header>
                <form class="login-form" action="/" method="POST">
                    <table>
                        <tr>
                            <td>Пользователь&nbsp;</td>
                            <?php
                            if ($user->user()) {
                            ?>
                                <td><b>&lap; <?php echo $user->getLogin(); ?> &gap;</b></td>
                                <td>
<!--                                    <pre class="hidden">-->
<!--                                        Ваши ID's:-->
<!---->
<!--                                        --><?php
//                                        var_dump($user->chatId());
//                                        ?>
<!--                                    </pre>-->
                                </td>
                            <?php
                            } else {
                            ?>
                                <td><input name="login" placeholder="user name" id="user"></td>
                                <td><input name="password" placeholder="password" id="user"></td>
                                <td><button id="ok">Ок</button></td>
                            <?php } ?>
                        </tr>
                    </table>
                </form>
            </header>
            <section>
                <div class="body messages" id="messages">
                    <div class="inner"></div>
                </div>
                <div class="message-area input-group">
                    <input class="form-control" disabled="disabled" maxlength="50" id="message" name="message">
                    <span class="input-group-addon btn btn-success" id="send">Send message</span>
                </div>
            </section>
        </div>
    </div>
</div>
</body>
</html>


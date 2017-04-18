<!DOCTYPE html>
<html>
<head>
    <title>Deimos Chat</title>
    {block css prepend}
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
        <link href="{'/bootstrap/css/bootstrap.min.css'|asset}" rel="stylesheet"/>
        <link href="{'/bootstrap/css/bootstrap-theme.min.css'|asset}" rel="stylesheet"/>
    {/block}
</head>
<body data-ws-domain="{$scheme}://{$host}:{$port}">
    {block content}{/block}
    {block script prepend}
        <!-- javascript -->
        <script src="{'/js/view.js'|asset}"></script>
    {/block}
</body>
</html>

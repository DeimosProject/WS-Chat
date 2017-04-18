<link href="{'/css/chat.css'|asset}" rel="stylesheet"/>

<div class="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <header class="login-form" data-login="{if $user}{$user->login}{/if}">
                    {if $user}
                        <span>Пользователь&nbsp;</span>
                        <span title="{$user->login}" class="btn user-config-btn">
<img src="{$user->avatar()}?s=24">
</span>
                        <div class="pull-right config-group">
                            <i class="btn fa fa-gears" data-toggle="modal" data-target="#user-settings-modal"></i>
                        </div>
                        <div class="clearfix"></div>
                    {else}
                        <form action="{'login'|route}" method="POST">
                            <div class="input-group col-xs-12">
                                <div class="col-sm-5 xol-xs-4">
                                    <div class="row">
                                        <input class="form-control" name="login" placeholder="user name" id="user">
                                    </div>
                                </div>
                                <div class="col-sm-5 col-xs-4">
                                    <div class="row">
                                        <input class="form-control" name="password" type="password"
                                               placeholder="password"
                                               id="password">
                                    </div>
                                </div>
                                <div class="col-sm-2 col-xs-4">
                                    <div class="row">
                                        <button class="form-control btn-warning" id="ok">Ок</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    {/if}
                </header>
                <div>
                    <div class="body messages" id="messages">
                        <div class="inner"></div>
                    </div>
                    <div class="message-area input-group">
                        <div class="form-control no-padding">
                            <input class="form-control" disabled="disabled" maxlength="50" id="message" name="message">
                            <select data-user-id="{$user->id|default:0}" class="form-control"
                                    id="send-to"></select>
                        </div>
                        <span class="input-group-addon btn btn-success" id="send">Send message</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 hidden-xs hidden-sm" id="users">
                <div class="row">
                    <div class="header">Пользователи онлайн:</div>
                    <div class="list"></div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    {if $user}
        <div id="user-settings-modal" tabindex="-1" role="dialog" class="modal fade">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                        <h4 class="modal-header-text">User settings</h4>
                    </div>
                    <div class="modal-body">
                        <div id="user-config-modal-wrapper">
                            <div class="col-xs-12">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="">Email</span>
                                        <input class="form-control data" name="email"
                                               value="{$user->email}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="">Password</span>
                                        <input class="form-control data" type="password" name="password"
                                               autocomplete="off"
                                               value="">
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <hr>
                                <div class="col-xs-6">
                                    <span class="save btn btn-success">Save</span>
                                </div>
                                <div class="col-xs-6">
                                    <a class="btn btn-warning" href="{'logout'|route}">Logout</a>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="alert-block">
                            <div class="alert-config-success alert alert-success alert-dismissible"
                                 style="display: none"
                                 role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <div>Success!</div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
    {/if}
</div>
<script src="//code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
<script src="{'/js/chat.js'|asset}"></script>
<script src="{'/bootstrap/js/bootstrap.min.js'|asset}"></script>
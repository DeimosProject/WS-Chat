{extends 'layout'}

{block content}
    <div class="container clearfix">
        <div class="people-list col-md-4">
            <div class="search input-group">
                <input type="text" class="form-control" placeholder="Search for...">
                <span class="input-group-addon">
                    <i class="fa fa-search"> </i>
                </span>
            </div>

            <div id="users-list"></div>
        </div>

        <div class="chat col-md-8">

            <div class="modal-view">
                <h1>Profile</h1>
                <hr/>
            </div>

            <div class="chat-header clearfix">
                <img src="https://avatars3.githubusercontent.com/u/25158980?v=3&s=40" alt="avatar"/>

                <div class="chat-about">
                    <div class="chat-with">Chat Deimos Project</div>
                    <div class="chat-num-messages" id="already"></div>
                </div>
                <i class="fa fa-star"></i>

                <div class="sign-out pull-right">
                    <i class="fa fa-user"></i>
                    <a href="#" id="profile" title="{$user->login}">
                        Profile
                    </a>
                </div>
            </div>

            <div class="chat-history scrollbar" id="messages">
                <div data-loader="timer"></div>
            </div>

            <div class="chat-message clearfix">
                <form id="message-to">
                    <textarea name="message-to-send" id="message-to-send" placeholder="Type your message"
                              rows="3"></textarea>

                    <button type="submit">send</button>
                    <button type="reset">reset</button>
                </form>
            </div>

        </div>

    </div>
{/block}

{block css}
    <link href="{'/css/view.css'|asset}" rel="stylesheet"/>
    <link href="{'/node_modules/css-loading/loaders.min.css'|asset}" rel="stylesheet"/>
{/block}

{block script}
    <script defer async src="{'/js/view.js'|asset}"></script>
{/block}

{extends 'layout'}

{block content}
    <div class="text-center" style="padding:50px 0">
        <div class="logo">login</div>
        <div class="login-form-1">
            <form action="{'login'|route}" method="post" class="text-left">
                <div class="login-form-main-message"></div>
                <div class="main-login-form">
                    <div class="login-group">

                        <div class="form-group">
                            <label for="lg_username" class="sr-only">Username</label>
                            <input type="text" class="form-control" id="lg_username" name="login"
                                   placeholder="username">
                        </div>

                        <div class="form-group">
                            <label for="lg_password" class="sr-only">Password</label>
                            <input type="password" class="form-control" id="lg_password" name="password"
                                   placeholder="password">
                        </div>

                    </div>

                    <button type="submit" class="login-button"><i class="fa fa-chevron-right"></i></button>

                </div>

                <div class="etc-login-form">
                    <p align="center">Deimos Project</p>
                </div>

            </form>
        </div>
    </div>
{/block}

{block css}
    <link href="{'/css/login.css'|asset}" rel="stylesheet"/>
{/block}

$(function () {
    var conn = null;
    var message = $('#message');
    var inner = $('#messages').find('.inner');
    var sendTo = $('#send-to');
    var userConfig = $('#user-config-modal-wrapper');
    var countErrors = 0;
    var wsHost = $('body').data('ws-domain');

    inner.data('counter', 0);

    function createConnection() {
        conn = new WebSocket(wsHost);

        init();
    }

    createConnection();

    function Message(response) {
        var maxMessages = 60;

        if (inner.data('counter') > maxMessages) {
            var msgs = inner.find('msg');
            for (var i = maxMessages; i < msgs.length; i++) {
                msgs.eq(i).remove();
            }
        }
        else {
            inner.data('counter', parseInt(inner.data('counter')) + 1);
        }

        var _class = '';
        if (response.hasOwnProperty('class')) {
            _class = ' ' + response['class'];
        }

        inner.prepend('<msg class="' + response.status + _class + '">' + response.text + '</msg>');
    }

    function Users(response) {
        var html = '<ul>';
        var sendToOptions = '';
        var myLogin = $('header.login-form').data('login');

        sendTo.html('<option value="0">Отправить всем</option>');

        $.each(response.users, function (key, item) {
            html += '<li class="' + (myLogin === item.login ? '' : 'send-to-user') + '" data-id="' + item.id + '"><img src="' + item.avatar + '?s=32"><span>' + item.login + '</span></li>';
            if (sendTo.data('user-id') === item.id)
            {
                return;
            }
            sendToOptions += '<option value="' + item.id + '">' + item.login + '</option>';
        });

        html += '<ul>';

        sendTo.append(sendToOptions);
        $('#users').find('.list').html(html);
    }

    function Setup(response) {
        if (response.hasOwnProperty('messages')) {
            var messages = response.messages;
            var myLogin = $('header.login-form').data('login');
            for (var i = messages.length; i >= 0; i--) {
                if (messages.hasOwnProperty(i)) {
                    var data = { };
                    var item = messages[i];
                    data.text = '<b>&lap; ' + item.login + ' &gap; </b> <i> ' + item.time + '</i>' + item.text;
                    data.status = 'history';
                    data.class = '';
                    if (item.login === myLogin) {
                        data.class = 'own';
                    }
                    Message(data);
                }
            }
        }
    }

    $('#send').click(function () {
        var $this = $(this);
        if (message.val() && !$this.attr('disabled')) {
            var data = {
                'text': message.val(),
                'to': sendTo.val()
            };

            conn.send(JSON.stringify(data));
            message.val('');
            $this.attr('disabled', 'disabled');
            message.attr('disabled', 'disabled');
            setTimeout(function () {
                $this.attr('disabled', false);
                message.attr('disabled', false);
            }, 500);
        }
    });

    message.keydown(function(e) {
        if(e.keyCode === 13)
        {
            $('#send').click();
        }
    });

    userConfig.find('.save').click(function () {
        var data = {};
        userConfig.find('input.data').each(function (i, e) {
            var $e = $(e);
            data[$e.attr('name')] = $e.val();
        });
        $.ajax({
            'url': '/save-config/',
            'data': data,
            'type': 'POST',
            'success': function (data) {
                $('.alert-config-success').show();
                setTimeout(function () {
                    $('.alert-config-success').hide();
                }, 1500);
            },
            'error': function (a, b) {
                console.dir(a);
                console.dir(b);
            }
        });
    });

    inner.on('click', 'msg.deleted', function () {
        $(this).hide(100).remove();
    });

    $('#users').on('click', '.send-to-user', function () {
        $('#send-to').prop('value', $(this).data('id'));
    });

    function init() {
        conn.onopen = function (e) {
            countErrors = 0;
            setTimeout(function () {
                $('msg.error.deleted').hide(300);
            }, 500);
            message.attr('disabled', false);
            var msg = {
                'status': 'info',
                'class': 'deleted',
                'text': 'Соединение установлено'
            };
            Message(msg);
        };

        conn.onclose = function (e) {
            message.attr('disabled', 'disabled');
            var sec = ['секунда', 'секунды', 'секунд'];
            var seconds = (countErrors + 1) * 3;
            var c = seconds % 100;
            if(c >= 11 && c <= 19) {
                c = sec[2];
            } else {
                c = c % 10;
                switch (c) {
                    case 1: c = sec[0]; break;
                    case 2:
                    case 3:
                    case 4: c = sec[1]; break;
                    default: c = sec[2];
                }
            }

            countErrors += 1;

            var msg = {
                'status': 'error',
                'class': 'deleted',
                'text': 'Произошла ошибка. Следующая попытка восстановления соединения через ' + seconds + ' ' + c
            };
            Message(msg);
            setTimeout(function () {
                createConnection();
            }, (seconds) * 1000);
        };

        conn.onmessage = function (e) {
            var data = JSON.parse(e.data);
            if (data.type === 'message') {
                Message(data);
            }
            else if (data.type === 'users') {
                Users(data);
            }
            else if (data.type === 'setup') {
                Setup(data);
                //} else if (data.type === 'setup') {
                //} else if (data.type === 'setup') {
            }
        };
    }
});

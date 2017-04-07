$(function () {
    var conn = null;
    var message = $('#message');
    var inner = $('#messages').find('.inner');
    var sendTo = $('#send-to');
    var userConfig = $('#user-config-modal-wrapper');

    inner.data('counter', 0);

    function createConnection() {
        conn = new WebSocket('ws://ws2.localhost:8080');

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

        sendTo.html('<option value="0">Отправить всем</option>');
        response.users.forEach(function (item, i) {
            if (!item.hasOwnProperty('image')) {
                item.image = '';
            }
            html += '<li class="send-to-user" data-id="' + item.id + '"><img src="//secure.gravatar.com/avatar/' + item.image + '?s=32"><span>' + item.login + '</span></li>';
            sendTo.append('<option value="' + item.id + '">' + item.login + '</option>');
        });
        html += '<ul>';

        $('#users').find('.list').html(html);
    }

    function Setup(response) {
        if (response.hasOwnProperty('messages')) {
            response.messages.forEach(function (item, i) {
                Message(item);
            });
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
            var msg = {
                'status': 'error',
                'class': 'deleted',
                'text': 'Произошла ошибка. Следующая попытка восстановления соединения через 3 секунды'
            };
            Message(msg);
            setTimeout(function () {
                createConnection();
            }, 3000);
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

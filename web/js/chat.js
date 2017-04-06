$(function () {
    var conn = new WebSocket('ws://ws2.localhost:8080');
    var message = $('#message');
    var inner = $('#messages').find('.inner');
    inner.data('counter', 0);

    conn.onopen = function(e) {
        message.attr('disabled', false);
        console.log("Connection established!");
    };

    conn.onclose = function (e) {
        alert('DANGER mazafaka!');
        window.location.reload();
    };

    function Message(response) {
        var maxMessages = 20;

        if(inner.data('counter') > maxMessages) {
            var msgs = inner.find('msg');
            for (var i=maxMessages; i < msgs.length; i++) {
                msgs.eq(i).remove();
            }
        } else {
            inner.data('counter', parseInt(inner.data('counter')) + 1);
        }

        var _class = '';
        if(response.hasOwnProperty('class')) {
            _class = ' ' + response['class'];
        }

        inner.prepend('<msg class="' + response.status + _class + '">' + response.text + '</msg>');
    }

    function Users(response) {
        var html = '<ul>';
        response.users.forEach(function (item, i) {
            html += '<li><img src="' + item.src + '">' + item.name + '</li>';
        });
        html += '<ul>';

        inner.find('.users').html(html);
    }

    function Setup(response) {
        //
    }

    conn.onmessage = function(e) {
        var data = JOSN.parse(e.data);
        if(data.type === 'message') {
            Message(data);
        } else if (data.type === 'users') {
            Users(data);
        } else if (data.type === 'setup') {
            Setup(data);
        //} else if (data.type === 'setup') {
        //} else if (data.type === 'setup') {
        }
    };

    $('#send').click(function () {
        var $this = $(this);
        if(message.val() && $this.attr('disabled')) {
            conn.send(message.val());
            message.val('');
            $this.attr('disabled', 'disabled');
            message.attr('disabled', 'disabled');
            setTimeout(function () {
                $this.attr('disabled', false);
                message.attr('disabled', false);
            }, 500);
        }
    });

    inner.on('click', 'msg.error', function () {
        $(this).hide(100).remove();
    });

});

import React from "react";
import ReactDOM from "react-dom";

let messages = [];
let users = [];
let webSocket;

class MessageComponent extends React.Component {

    static map(msg) {

        if (msg.own === true) {
            return <li key={msg.id} className="clearfix">
                <div className="message-data align-right">
                    <span className="message-data-time">{msg.createdAt}</span> &nbsp; &nbsp;
                    <span className="message-data-name">{msg.login}</span> <i className="fa fa-circle me" />
                </div>
                <div className="message other-message float-right" dangerouslySetInnerHTML={{__html: msg.message}} />
            </li>
        }

        return <li key={msg.id}>
            <div className="message-data">
                <span className="message-data-name"><i className="fa fa-circle online" /> {msg.login}</span>
                <span className="message-data-time">{msg.createdAt}</span>
            </div>
            <div className="message my-message" dangerouslySetInnerHTML={{__html: msg.message}} />
        </li>;

    }

    render() {
        ReactDOM.render(
            <span>{this.props.messages.length}</span>,
            document.getElementById('already')
        );

        return <ul>
            {this.props.messages.map(msg => MessageComponent.map(msg))}
        </ul>;
    }

}

class UserComponent extends React.Component {

    static map(user) {

        return <li key={user.login} className="clearfix">
            <img src={user.avatar + "?s=40"} alt={user.login}/>
            <div className="about">
                <div className="name">{user.login}</div>
                <div className="status">
                    <i className="fa fa-circle online">&nbsp;</i> online
                </div>
            </div>
        </li>;

    }

    render() {
        users = this.props.users; // update list
        return <ul className="list scrollbar">
            {this.props.users.map(user => UserComponent.map(user))}
        </ul>;
    }

}

function chatRender(messages) {
    let element = document.getElementById('messages');

    ReactDOM.render(
        <MessageComponent messages={messages}/>,
        element
    );

    element.scrollTop = element.scrollHeight;
}

function userRender(users) {
    ReactDOM.render(
        <UserComponent users={users}/>,
        document.getElementById('users-list')
    );
}

function onMessage(e) {
    let target = parse(e);

    switch (target.type) {
        case 1: // message
            target.data.own = target.own;
            messages[target.data.id] = target.data;
            chatRender(messages);
            break;

        case 2: // any: messages, ...

            if (typeof target.data[0].message !== "undefined") {
                for (const i in target.data) {
                    if (!target.data.hasOwnProperty(i)) {
                        continue;
                    }

                    messages[target.data[i].id] = target.data[i];
                }

                chatRender(messages);
            }
            break;

        case 3: // user list
            console.log(target);
            userRender(target.data);
            break;
    }
}

function parse(e) {
    return JSON.parse(e.data);
}

function sendMessage() {
    let element = document.getElementById('message-to-send');
    if (webSocket && element.value.trim().length) {
        webSocket.send(element.value.trim());
        element.value = '';
    }
}

(function ws() {

    let configure = document.getElementsByTagName('body')[0].dataset;

    webSocket = new WebSocket(configure.wsDomain);

    webSocket.onopen = function () {
        ReactDOM.render(
            <div data-loader="timer">&nbsp;</div>,
            document.getElementById('messages')
        );
        console.log('You are connected');
    };

    webSocket.onmessage = onMessage;

    webSocket.onclose = function () {
        ReactDOM.render(
            <div data-loader="ball-auto">&nbsp;</div>,
            document.getElementById('messages')
        );

        requestAnimationFrame(ws);
    };

})();

document.getElementById('message-to').onsubmit = (e) => {
    e.preventDefault();
    sendMessage();
};

document.getElementById('message-to-send').onkeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && (e.keyCode === 13 || e.keyCode === 10)) {
        sendMessage();
    }
};

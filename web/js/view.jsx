import React from "react";
import ReactDOM from "react-dom";

class CommentComponent extends React.Component {

    message(message) {

        if (message.own) {
            return <li key={message.id} className="clearfix">
                <div className="message-data align-right">
                    <span className="message-data-time">{message.time}</span> &nbsp; &nbsp;
                    <span className="message-data-name">{message.login}</span> <i className="fa fa-circle me"> </i>

                </div>
                <div className="message other-message float-right">{message.message}</div>
            </li>
        }

        return <li key={message.id}>
            <div className="message-data">
                <span className="message-data-name"><i className="fa fa-circle online"> </i> {message.login}</span>
                <span className="message-data-time">{message.time}</span>
            </div>
            <div className="message my-message">{message.message}</div>
        </li>;

    }

    render() {
        ReactDOM.render(
            <span>{this.props.messages.length}</span>,
            document.getElementById('already')
        );

        return <ul>
            {this.props.messages.map(message => this.message(message))}
        </ul>;
    }

}

let messages = [
    {
        id: 1,
        time: (new Date()).getTimezoneOffset(),
        login: 'Serg',
        message: 'Hi!',
        own: false
    },
    {
        id: 2,
        time: (new Date()).getTimezoneOffset(),
        login: 'Max',
        message: 'Hi!',
        own: true
    },
];

ReactDOM.render(
    <CommentComponent messages={messages}/>,
    document.getElementById('messages')
);

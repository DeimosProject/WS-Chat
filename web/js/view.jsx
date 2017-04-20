import React from "react";
import ReactDOM from "react-dom";

class CommentComponent extends React.Component {

    static map(msg) {

        if (msg.own) {
            return <li key={msg.id} className="clearfix">
                <div className="message-data align-right">
                    <span className="message-data-time">{msg.createdAt}</span> &nbsp; &nbsp;
                    <span className="message-data-name">{msg.login}</span> <i className="fa fa-circle me"> </i>

                </div>
                <div className="message other-message float-right">{msg.message}</div>
            </li>
        }

        return <li key={msg.id}>
            <div className="message-data">
                <span className="message-data-name"><i className="fa fa-circle online"> </i> {msg.login}</span>
                <span className="message-data-time">{msg.createdAt}</span>
            </div>
            <div className="message my-message">{msg.message}</div>
        </li>;

    }

    render() {
        ReactDOM.render(
            <span>{this.props.messages.length}</span>,
            document.getElementById('already')
        );

        return <ul>
            {this.props.messages.map(msg => CommentComponent.map(msg))}
        </ul>;
    }

}

// let messages = [
//     {
//         id: 1,
//         login: 'Serg',
//         message: 'Hi!',
//         own: false,
//         createdAt: (new Date()).getUTCMilliseconds(),
//     },
//     {
//         id: 2,
//         login: 'Max',
//         message: 'Hi!',
//         own: true,
//         createdAt: (new Date()).getUTCMilliseconds(),
//     },
// ];

function chatRender(messages) {
    ReactDOM.render(
        <CommentComponent messages={messages}/>,
        document.getElementById('messages')
    );
}

// chatRender([
//     {
//         id: 1,
//         login: 'Hello',
//         message: 'Hello World',
//         own: false,
//         createdAt: (new Date()).now
//     }
// ]);

export { chatRender };

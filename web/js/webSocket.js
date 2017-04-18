;(function (window, document, undefined) {

    var webSocket = function (url, options) {

        this.conn = new WebSocket(url);

        this.message = function () {
            // options['message'](this);
        };

        if (this.prototype !== undefined) {
            this.fn = this.prototype;
        }
        else {
            this.fn = this.__proto__;
        }

        return this;

    };

    window.fWS = webSocket.fn;
    window.dWS = webSocket;

})(window, document);

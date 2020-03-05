var express = require('express');
var app = express();
var fs = require('fs');
var server = require('https').createServer({
    key: fs.readFileSync('/etc/ssl/private/newtmobi.key'),
    cert: fs.readFileSync('/etc/ssl/newt_mobi.crt'),
    secure: true,
    requestCert: true,
    rejectUnauthorized: false
}, app);
var io = require('socket.io')(server, {'transports' : ['polling'], 'pingInterval': 5000, 'pingTimeout': 10000});
var port = process.env.PORT || 3000;

var timeout = require('callback-timeout');
var moment = require('moment-timezone');
// var express = require('express');
// var app = express();
// var fs = require('fs');
// var server = require('http').createServer(app);
// var io = require('socket.io')(server, {'transports' : ['polling'], 'pingInterval': 5000, 'pingTimeout': 10000});
// var port = process.env.PORT || 3000;
//
// var timeout = require('callback-timeout');
// var moment = require('moment-timezone');

app.get('/', function(req, res) {
    res.sendFile(__dirname + '/index.html');
});

var clients = [];
var queues = {};
var read_queues = {};

server.listen(port, function () {
    console.log('Server listening at port %d', port);
});

io.on('connection', function (socket) {
    var user = JSON.parse(socket.handshake.query.user);
    if (user != null) {
        clients[user.code] = {
            'socket': socket.id
        };
        console.log(user.name + ' (' + socket.id + ') connected');
    } else {
        socket.disconnect();
    }

    socket.on('load_read_queue', function (callback) {
        if (typeof callback === "function") {
            var reads = loadReadQueue();
            callback(1234, {reads: reads});
        }
    });

    socket.on('read_queue_loaded', function (callback) {
        if (typeof callback === "function") {
            if (typeof read_queues[user.code] != 'undefined') {
                delete read_queues[user.code];
            }
            callback(1234);
        }
    });

    socket.on('join_dialog', function (data) {
        socket.join(data.dialog);
        console.log(socket.id + ' joined ' + data.dialog);

        setRead(data);
    });

    socket.on('leave_dialog', function (data) {
        socket.leave(data.dialog);
        console.log(socket.id + ' left ' + data.dialog);
    });

    socket.on('message', function (data, callback) {
        if (typeof callback === "function") {
            console.log(socket.id + ' sends message');

            data['date'] = moment.tz('Asia/Jakarta').toDate();
            var room = getDialogId(data.sender_code, data.receiver_code, data.lesson_id);
            sendMessage(socket, data, room, user, callback);
        }
    });

    socket.on('typing', function (data) {
        console.log(socket.id + ' is typing...');
        if (Object.keys(io.sockets.adapter.sids[socket.id]).length > 1) {
            socket.broadcast.to(Object.keys(io.sockets.adapter.sids[socket.id])[1]).emit('typing', data);
        }
    });

    socket.on('stop_typing', function (data) {
        console.log(socket.id + ' stops typing...');
        if (Object.keys(io.sockets.adapter.sids[socket.id]).length > 1) {
            socket.broadcast.to(Object.keys(io.sockets.adapter.sids[socket.id])[1]).emit('stop_typing', data);
        }
    });

    socket.on('is_active', function (data, callback) {
        if (typeof callback === "function") {
            var room = data.dialog;
            if (data.receiver_code in clients && typeof io.sockets.adapter.rooms[room] != 'undefined') {
                var active = io.sockets.adapter.rooms[room].sockets[clients[data.receiver_code].socket];
                if (typeof active != 'undefined') {
                    callback(1234, {active: true, dialog: room});
                } else {
                    callback(1234, {active: false, dialog: room});
                }
            } else {
                callback(1234, {active: false, dialog: room});
            }
        }
    });

    socket.on('check_online', function (users, callback) {
        if (typeof callback === "function") {
            var results = [];
            for (var i = 0; i < users.length; i++) {
                if (clients[users[i].receiver_code] != null) {
                    var usero = {
                        code: users[i].receiver_code,
                        online: true
                    };
                } else {
                    usero = {
                        code: users[i].receiver_code,
                        online: false
                    };
                }
                results.push(usero);
            }
            callback(1234, results);
        }
    });

    socket.on('disconnect', function () {
        console.log(user.name + ' (' + socket.id + ') disconnected');

        if (typeof user != 'undefined') {
            if (typeof clients[user.code] != 'undefined') {
                delete clients[user.code];
            }
        }
    });

    function sendMessage(socket, data, room, user, callback) {
        if (data.receiver_code in clients) {
            io.sockets.connected[clients[data.receiver_code].socket].emit('message', data, timeout(function handler(err) {
                if (err) {
                    sendMessage(socket, data, room, user, callback);
                } else {
                    callback(1234, data.unique_code, 1);
                }
            }, 5000));
        } else {
            callback(1234, data.unique_code, 0);
        }
    }

    function setRead(data) {
        if (data.receiver_code in clients) {
            io.sockets.connected[clients[data.receiver_code].socket].emit('is_active', {
                active: true,
                dialog: data.dialog
            }, timeout(function handler(err) {
                if (err) {
                    setRead(data)
                }
            }, 2500));
        } else {
            addReadQueue(data.receiver_code, data.dialog);
        }
    }

    function loadQueue() {
        var dialogs = [];
        if (typeof queues[user.code] != 'undefined') {
            var keys = Object.keys(queues[user.code]);
            for (var i = 0; i < keys.length; i++) {
                var dialog = {
                    id: keys[i],
                    user: queues[user.code][keys[i]].sender,
                    chats: queues[user.code][keys[i]].chats
                };
                dialogs.push(dialog);
            }
        }
        return dialogs;
    }

    function loadReadQueue() {
        var reads = [];
        if (typeof read_queues[user.code] != 'undefined') {
            var keys = read_queues[user.code];
            for (var i = 0; i < keys.rooms.length; i++) {
                var dialog = {
                    id: keys.rooms[i]
                };
                reads.push(dialog);
            }
        }
        return reads;
    }

    function getDialogId(sender, receiver, lesson) {
        var dialog;
        if (sender.split('_')[0] == 'STU' && receiver.split('_')[0] == 'TEA') {
            dialog = sender + '-' + receiver + '-' + lesson;
        } else {
            dialog = receiver + '-' + sender + '-' + lesson;
        }
        return dialog;
    }

    function addQueue(receiver, room, sender, chat) {
        var chats;
        if (typeof queues[receiver] != 'undefined') {
            if (typeof queues[receiver][room] != 'undefined') {
                chats = queues[receiver][room].chats;
            } else {
                queues[receiver][room] = {
                    sender: sender,
                    chats: []
                };
                chats = queues[receiver][room].chats;
            }
        } else {
            queues[receiver] = {};
            queues[receiver][room] = {
                sender: sender,
                chats: []
            };
            chats = queues[receiver][room].chats;
        }
        chats.push(chat);
        chats.sort(function (a, b) {
            return a.date - b.date;
        });
    }

    function addReadQueue(receiver, room) {
        var rooms;
        if (typeof read_queues[receiver] == 'undefined') {
            read_queues[receiver] = {
                rooms: []
            };
            rooms = read_queues[receiver].rooms;
        } else {
            rooms = read_queues[receiver].rooms;
        }
        if (rooms.indexOf(room) == -1) {
            rooms.push(room);
        }
    }
});
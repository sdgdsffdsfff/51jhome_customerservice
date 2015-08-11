var Redis = require('ioredis');
var redis_client = new Redis();

var io = require('socket.io')();

io.sockets.on('connection', function(socket){
    
});
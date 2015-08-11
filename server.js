var Redis = require('ioredis'),
    redis_client = new Redis(),
    EventEmitter = require('events').EventEmitter,
    path = require('path');

var event = new EventEmitter();

var io = require('socket.io')();
var Scripto = require('redis-scripto');
var scriptManager = new Scripto(redis_client);
scriptManager.loadFromDir(path.join(__dirname, './lua_scripts'));

/* 存储客户信息的对象 */
var clients = {};
/* 存储客服的对象 */
var servers = {};

var msg = {
    'redis_err': 'redis错误'
};

var needNum = 10;

function getMinum(){
    var min = 1000;
    var minUid = null;

    for(var uid in servers){
        if(servers[uid].n < min){
            min = servers[uid].n;
            minUid = uid;
        }
    }
    return minUid;
}

//分发总队列中的客户
event.on('distribution', function(){
    for(var i = 0; i < needNum; i++){
        var minUid = getMinum();
        if(minUid){
            var server = servers[minUid];
            server.n += 1;
            redis_client.rpop('cs_line', function(err, customerid){
                var client = clients[customerid];
                console.log(minUid);
                client.server = minUid;
                server.clients[client] = 1;
                client.socket.emit('server_in', {name: server.name, id: server.id});
                server.socket.emit('client_in', {name: client.name, id: client.id});

                client.socket.on('msg', function(data){
                    var clientid = data.clientid;
                    var serverid = data.serverid;
                    if(clientid == clients[clientid].socket.clientid && servers[serverid].clients[clientid] == 1){
                        servers[serverid].socket.emit({msg: data.msg});
                    }
                });

                server.socket.on('msg', function(data){
                    var clientid = data.clientid;
                    if(server.clients[clientid] == 1){
                        clients[clientid].socket.emit({msg: data.msg});
                    }
                });
                

            });

        }
        
    }
});

function close(socket, msg){
    socket.disconnect();
}



io.sockets.on('connection', function(socket){
    /*
     * data 结构
     * data.uid 用户uid
     */
    socket.on('customer', function(data){
        //TODO: 第一步对于连接的客户进行身份验证
        if(typeof data.uid !== "string" && typeof data.uid !== "number" ){
            console.log(data);
            return close(socket);
        }
        socket.uid = data.uid;
        if(clients[data.uid] != undefined){
            return close(socket);
        }
        clients[data.uid] = {
            "socket": socket
        };
        redis_client.llen('cs_line', function(err, len){
            if(err){
                return close(socket, msg.redis_err);
            }
            redis_client.lpush('cs_line', data.uid, function(err){
                if(err){
                    return close(socket, msg.redis_err);
                }
            });
            if(len == 0){
                socket.emit('connret', {s: 0, m: 'waiting'});
            }
            event.emit('distribution', {num: (len+1)});
        });

        socket.on('disconnect', function(){
            //删除对应客服中的客户
            delete servers[clients[data.ui].server].clients[data.uid];
            delete clients[data.uid];

        });
    }); 

    socket.on('service', function(data){
        if(typeof data.uid === "string" || typeof data.uid === "number"){
            // 又可以接入10个人啦
            needNum += 10;
            servers[dat.uid] = {socket: socket, clients : {}, n: 0};
            event.emit('distribution');
        }
    });
});

redis_client.flushall(function(){
    console.log('flushall');
    io.listen(8083);
});

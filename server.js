var Redis = require('ioredis'),
    redis_client = new Redis(),
    EventEmitter = require('events').EventEmitter,
    path = require('path'),
    async = require('async');

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

var needNum = 0;
var capacity = 10;

function getMinum(){
    var min = 1000;
    var minUid = null;
    for(var uid in servers){
        if(servers[uid].status == 1 && servers[uid].n <= 10 && servers[uid].n < min){
            min = servers[uid].n;
            minUid = uid;
        }
    }
    return minUid;    
}
var log = function(msg){
    var date = new Date();
    var dstring = date.getFullYear() + "-" + date.getMonth() + "-" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes() + ":" + (parseInt(date.getSeconds()) <  10 ? '0' + date.getSeconds() : date.getSeconds());
     console.log("[" + dstring + "] Info: " + msg);
}
//分发总队列中的客户
event.on('distribution', function(){
    log('distribution process: needNum[' + needNum + ']');
    redis_client.llen('cs_line', function(err, len){
        if(err){
            return log('redis 错误');
        }
        log('cs_line len ' + len);
        var min = needNum > len ? len : needNum;
        log("min: " + min);
        for(var i = 0; i < min; i++){
            var minUid = getMinum();

            if(minUid){
                var server = servers[minUid];
                server.n += 1;
                (function(minUid, server){
                    redis_client.rpop('cs_line', function(err, customerid){
                        if(customerid){
                            log("customerid: " + customerid + " pop from the cs_line to the minUid:" + minUid);
                            var client = clients[customerid];
                            client.serverid = minUid;
                            server.clients[customerid] = 1;
                            client.socket.emit('server_in', {servername: server.servername, serverid: server.serverid});
                            server.socket.emit('client_in', {username: client.username, uid: client.uid});
                        }else{
                             log('no client in cs_line');
                        }                                               
                    });
                })(minUid, server);
            }else{
                log('no minUid');
                break;
            }        
        }
    });
    
    log('distribution ended');
});

function close(socket, msg){
     log('socket close with msg:' + msg);
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
            log(data);
            return close(socket);
        }
        socket.uid = data.uid;
        if(typeof clients[data.uid] != "undefined"){
            return close(socket, '该uid已经存在');
        }
        clients[data.uid] = {
            "socket": socket,
            "username" : data.username,
            "uid" : data.uid,
            "lastactive": (new Date()).getTime()
        };
        redis_client.llen('cs_line', function(err, len){
            if(err){
                return close(socket, msg.redis_err);
            }

            if(len > 0 || needNum == 0){
                socket.emit('connret', {s: 0, m: 'waiting'});
            }else{
                socket.emit('connret', {s: 1, m: '马上就有客服了'});
            }

            redis_client.lpush('cs_line', data.uid, function(err, lenafter){
                if(err){
                    return close(socket, msg.redis_err);
                }
                log("lenafter " + lenafter);
                event.emit('distribution');
            });            

            socket.on('msg', function(data){
                var clientid = socket.uid;
                log("clientid [" + clientid + "] in msg");
                var serverid = clients[clientid].serverid;
                clients[clientid].lastactive = (new Date()).getTime();
                servers[serverid].socket.emit('client_msg', {msg: data.msg, clientid: clientid});
            });
        });

        socket.on('disconnect', function(){
            //删除对应客服中的客户
            if(clients[data.uid].serverid){
                servers[clients[data.uid].serverid].socket.emit("client_disconnect", {clientid: data.uid});
                delete servers[clients[data.uid].serverid].clients[data.uid];
            }
            delete clients[data.uid];
            console.log('client disconnect');
        });
    }); 

    socket.on('service', function(data){
        if(typeof data.uid !== "string" && typeof data.uid !== "number"){
            return close(socket, 'data uid not valid');
        }
        /* TODO： 还要进行客服的身份验证 */
        var serverid = data.uid;

        if(typeof servers[serverid] !== "undefined"){
            return close(socket, "客服uid 已经存在");
        }

        // 又可以接入10个人啦
        needNum += 10;
        servers[data.uid] = {socket: socket, servername: data.servername, serverid: data.uid, clients : {}, n: 0, status: 1};
        event.emit('distribution');

        socket.on('msg_from_server', function(data){
            log("server_msg");
            console.log(data);
            var clientid = data.clientid+'';
            if(servers[serverid].clients[clientid] == 1){
                log("send msg["+ data.msg+"] to client["+ clientid+"]")
                clients[clientid].socket.emit('service_msg', {msg: data.msg});
            }
        });
        // 客服变换状态的请求
        socket.on('status', function(data){
            servers[serverid].status = data.status;
            if(data.status == 0){
                needNum -= (capacity - servers[serverid].n);
            }else{
                needNum += (capacity - servers[serverid].n);
            }            
        });
        /* 客服离线 */
        socket.on('disconnect', function(){
            var clientids = [];
            for(var clientid in servers[serverid].clients){
                clientids.push(clientid);
            }
            log("clientids");
            console.log(clientids);
            async.forEach(clientids, function(item){
                clients[item].socket.emit('server_disconnect');
            }, function(err){
                log('error in async');
                console.log(err);
            });
            delete servers[serverid];
        });
    });
});

redis_client.flushall(function(){
     log('flushall');
    io.listen(8083);
});

setInterval(function(){
    var now = (new Date()).getTime();
    var maxLiveTime = 1000*60*5;
    for(var clientid in clients){
        if((now - clients[clientid].lastactive) > maxLiveTime){
            clients[clientid].socket.disconnect();
        }
    }
}, 60000);

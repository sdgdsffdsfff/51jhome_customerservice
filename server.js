var Redis = require('ioredis'),
    redis_client = new Redis(),
    EventEmitter = require('events').EventEmitter,
    path = require('path'),
    async = require('async');

var event = new EventEmitter();

var Chat = require('./model/chat.js');

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
var capacity = 2;

function getMinum(){
    var min = 1000;
    var minUid = null;
    var uids = [];
    for(var uid in servers){
        if(servers[uid].status == 1 && servers[uid].live && servers[uid].n <= 10 ){
            if(servers[uid].n < min){
                min = servers[uid].n;
                minUid = uid;
                uids.push(uid);
            }else if(servers[uid].n == min){
                //若存在相同情况，则加入数组随机一个
                uids.push(uid);
            }
        }
    }
    if(uids.length > 1){
        return uids[parseInt(Math.random()*uids.length)];
    }else{
        return minUid;  
    }   
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
                needNum -= 1;
                (function(minUid, server){
                    redis_client.rpop('cs_line', function(err, customerid){
                        if(customerid){
                            log("customerid: " + customerid + " pop from the cs_line to the minUid:" + minUid);
                            var client = clients[customerid];
                            client.serverid = minUid;
                            server.clients[customerid] = 1;
                            Chat.init(customerid, minUid, function(err){
                                if(err){
                                    console.log(err);
                                }
                                client.socket.emit('server_in', {servername: server.servername, serverid: server.serverid});
                                server.socket.emit('client_in', {username: client.username, uid: client.uid, cur_n: server.n});
                            });
                            
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
        log('aa');
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

            socket.on('message', function(data){
                var clientid = socket.uid;
                log("clientid [" + clientid + "] in msg");
                var serverid = clients[clientid].serverid;
                clients[clientid].lastactive = (new Date()).getTime();
                Chat.insert({clientid: clientid, serverid: serverid, whosaid: 2, chatcontent: data.content}, function(err){
                    if(err){
                        log("client msg Chat insert error");
                        console.log(err);
                    }
                    servers[serverid].socket.emit('client_msg', {msg: data.content, clientid: clientid});
                });                
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
            // live true 该客服已经上线
            if(servers[serverid].live == true){
                return close(socket, "客服uid 已经存在");
            }else{
                console.log("客服断线重连");

                var server = servers[serverid];
                var clientids = [];
                for(var clientid in server.clients){
                    clientids.push(clientid);
                }
                for(var i = 0; i < clientids.length; i++){
                    var client = clients[clientids[i]];
                    socket.emit('client_in', {username: client.username, uid: client.uid, cur_n: server.n});
                    client.socket.emit('server_in', {servername: server.servername, serverid: server.serverid});
                }
                server.live = true;
                server.status = 1;
                needNum += (capacity - server.n);
            }            
        }else{
            // 又可以接入若干个人啦
            needNum += capacity;
            servers[data.uid] = {
                socket: socket, 
                servername: data.servername, 
                serverid: data.uid, 
                clients : {}, 
                n: 0, 
                status: 1, /*用来表示离开或者在线 */
                live: true /* socket是否在线 */
            };
            event.emit('distribution');
        }

        

        socket.on('msg_from_server', function(data){
            log("server_msg");
            console.log(data);
            var clientid = data.clientid+'';
            if(servers[serverid].clients[clientid] == 1){
                log("serverid["+serverid+"] servername["+servers[serverid].servername+"] send msg["+ data.msg+"] to client["+ clientid+"]");
                Chat.insert({clientid: clientid, serverid: serverid, whosaid: 1, chatcontent: data.msg}, function(err){
                    if(err){
                        log("Chat insert error");
                        console.log(err);
                    }
                    clients[clientid].socket.emit('service_msg', {msg: data.msg});
                });                
            }
        });
        socket.on('getservers', function(){
            var olservers = [];
            for(var i in servers){
                if(serverid != i){
                    olservers.push({serverid: i, servername: servers[i].servername});
                }                
            }
            console.log("olservers");
            console.log(olservers);
            socket.emit('getserversret', olservers);
        });
        var forward = function(clientid, targetid){
            if(typeof clients[clientid] !== "undefined" && typeof servers[targetid] !==  "undefined"){
                console.log('forward success');
                delete servers[serverid].clients[clientid];
                servers[serverid].n--;

                servers[targetid].clients[clientid] = 1;
                servers[targetid].n ++;

                clients[clientid].serverid = targetid;
                clients[clientid].socket.emit('server_in', {serverid: targetid, servername: servers[targetid].servername});
                servers[targetid].socket.emit('client_in', {
                    username: clients[clientid].username, 
                    uid: clients[clientid].uid, 
                    cur_n: servers[targetid].n
                });
            }else{
                socket.emit('forward_ret', {status: -1});
            }
        }
        /* 转至其他客服*/
        socket.on('forward', function(data){
            console.log('forward');
            var targetid = data.serverid,
                clientid = data.clientid;
            forward(clientid, targetid);
        });
        socket.on('client_offline', function(data){
            var clientid = data.clientid;
            delete servers[serverid].clients[clientid];
            clients[clientid].socket.emit('offline', {msg: '感谢对结邻公社的支持!'});
        });
        // 客服变换状态的请求
        socket.on('status', function(data){
            if(servers[serverid].status != parseInt(data.status)){
                servers[serverid].status = data.status;
                if(data.status == 0){
                    needNum -= (capacity - servers[serverid].n);
                }else{
                    needNum += (capacity - servers[serverid].n);
                }            
                log("server status: " + servers[serverid].status);
                log("needNum: " + needNum);
            }else{
                log('状态已经切换过了');
                log("status[" + data.status + "]");
            }
        });

        /* 客服掉线 */
        socket.on('disconnect', function(){
            var clientids = [];
            for(var clientid in servers[serverid].clients){
                clientids.push(clientid);
            }
            servers[serverid].live = false;
            log("clientids");
            console.log(clientids);
            if(clientids.length > 0){
                async.forEach(clientids, function(item){
                    clients[item].socket.emit('server_disconnect');
                    var targetid = getMinum();
                    if(targetid){
                        forward(item, targetid);
                    }
                }, function(err){
                    if(err){
                        log('error in async');
                        console.log(err);    
                    }
                });
            }else{
                log("server "+servers[serverid].servername+" off line");
            }
        });
    });
});

redis_client.flushall(function(){
     log('flushall');
    io.listen(8083);
});


setInterval(function(){
    var now = (new Date()).getTime();
    var maxLiveTime = 1000*60*5*10;
    for(var clientid in clients){
        if((now - clients[clientid].lastactive) > maxLiveTime){
            clients[clientid].socket.disconnect();
        }
    }
}, 60000);

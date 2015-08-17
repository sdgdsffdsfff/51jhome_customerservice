var mongodb = require('./db.js');

var Chat = {};

Chat.insert = function(content, callback){
    var clientid = content.clientid,
        serverid = content.serverid,
        chatcontent = content.chatcontent,
        whosaid = content.whosaid;

    mongodb.open(function(err, db){
        if(err){
            return callback(err);
        }
        db.collection('chats', function(err, collection){
            if(err){
                mongodb.close();
                return callback(err);
            }
            collection.update({clientid: clientid, serverid: serverid}, 
                {
                    $push: 
                        {contents:
                            {
                                content: chatcontent,
                                time: parseInt((new Date()).getTime()/ 1000),
                                whosaid: whosaid
                            }
                        }
                }, function(err, ret){
                if(err){
                    mongodb.close();
                    return callback(err);
                }
                callback(null);
            });
        })
    });
};

Chat.init = function(clientid, serverid, clientname, servername,callback){
    clientid = clientid+'';
    serverid = serverid+'';
    mongodb.open(function(err, db){
        if(err){
            return callback(err);
        }

        db.collection('chats', function(err, collection){
            if(err){
                mongodb.close();
                return callback(err);
            }

            collection.findOne({clientid: clientid, serverid: serverid}, {"contents": 0},function(err, ret){
                if(err){
                    mongodb.close();
                    return callback(err);
                }
                if(ret == null){
                    collection.insert({clientid: clientid, serverid: serverid, clientname: clientname, servername: servername, contents: []},{
                        safe: true
                    }, function(err){
                        mongodb.close();
                        callback(null);
                    });
                }else{
                    mongodb.close();
                    callback(null);
                }
            });

            
        });
    });
};

module.exports = Chat;

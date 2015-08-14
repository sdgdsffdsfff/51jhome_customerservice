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
                                time: parseInt((new Date()).getTime()/ 1000)
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

Chat.init = function(clientid, serverid, callback){
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
                    collection.insert({clientid: clientid, serverid: serverid, contents: []},{
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

Chat.history = function(clientid, serverid, page, callback){
    var page_size = 10;
    mongodb.open(function(err, db){
        if(err){
            return callback(err);
        }
        db.collection('chats', function(err, collection){
            if(err){
                mongodb.close();
                return callback(err);
            }

            collection.findOne({clientid: clientid, serverid: serverid}, {}, function(err, ret){
                if(err){
                    mongodb.close();
                    return callback(err);
                }
                if(ret == null || ret.contents.length == 0){
                    return null;
                }else{
                    var total = ret.cotents.length;
                    var start = page_size * (page - 1);
                    var end = start + page_size - 1;
                    start = start >= total ? total : start;
                    end = end >= total ? total : end;
                    callback(ret.contents.slice(start, end), total, page);
                }
            });
        });
    });
};
module.exports = Chat;

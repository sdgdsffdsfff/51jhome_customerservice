$(function() {
    var g_appMsg;
    var g_delResId;
    $("#file_upload").omFileUpload({
        action: postPicUrl,
        fileExt: "*.jpg;*.png;*.gif;*.jpeg;*.bmp",
        fileDesc: "Image Files",
        sizeLimit: 2 * 1024 * 1024,
		onSelect:function(a,b,c){
			//console.log(a);console.log(b);console.log(c);
			var p = Math.round(b.size / 1024 * 100) * 0.01;
				var q = "KB";
				if (p > 1000) {
					p = Math.round(p * 0.001 * 100) * 0.01;
					q = "MB"
				}
			  console.log(p.toFixed(2)+q);	
			  $("#upload-size").html(p.toFixed(2)+q);
		},
        onError: function(ID, fileObj, errorObj, event) {
            if (errorObj.type == "File Size") {
                Msg.error("\u4e0a\u4f20\u56fe\u7247\u7684\u5927\u5c0f\u4e0d\u80fd\u8d85\u8fc7\u0032\u004d");
            }else{
				console.log(errorObj);
				Msg.error(JSON.stringify(errorObj));	
			}
        },
        autoUpload: true,
        swf: UEDITOR_HOME_URL + "swf/om-fileupload.swf",
        method: "POST",
        actionData: {},
        onComplete: function(ID, fileObj, response, data, event) {
			$("#upload-progress").hide();
            console.log(response);
			try{
            	var jsonData = $.parseJSON(response);
			}catch(e){
				Msg.error(e);
			}
            if (jsonData.result == "error") {
                Msg.error(jsonData.msg);
                return false
            }
            $(".default-tip", g_appMsg).hide();
            $(".i-img", g_appMsg).attr("src", jsonData.fileUrl).show();
            $("#imgArea").show().find("#img").attr("src", jsonData.fileUrl);
            $(".cover", g_appMsg).val(jsonData.savePath);
            $(".coverurl", g_appMsg).val(jsonData.fileUrl);
        },
		onProgress:function(a,b,c,d){
			//console.log(a);console.log(b);console.log(d);
			console.log(c);
			var speed=c.speed;
			$("#upload-progress").show().html('已完成：<span class="red">'+c.percentage+'</span>%，速度：<span class="red">'+speed.toFixed(2)+'</span>KB/s');
		}
    });
    $("#title").bind("keyup",function() {
        $(".i-title", g_appMsg).text($(this).val());
        $(".title", g_appMsg).val($(this).val())
    });
    $("#title").blur(function() {
        $(".title", g_appMsg).val($(this).val())
    });
	$("#author").bind("keyup",function() {
        $(".author", g_appMsg).val($(this).val())
    });
    $("#author").blur(function() {
        $(".author", g_appMsg).val($(this).val())
    });
	$("#isCover").click(function(){
		if($(this).is(":checked")){
			$(".isCover", g_appMsg).val(1);
		}else{
			$(".isCover", g_appMsg).val(0);
		}
	});
    $("#source_url").bind("keyup", function() {
        $(".sourceurl", g_appMsg).val($(this).val())
    });
    $("#source_url").blur(function() {
        $(".sourceurl", g_appMsg).val($(this).val())
    })
    $("#url-block-link").click(function() {
        $("#url-block").show();
        $(this).hide()
    });
    $("#delImg").click(function() {
        $(".default-tip", g_appMsg).show();
        $("#imgArea").hide();
        $(".cover,.coverurl", g_appMsg).val("");
        $(".i-img", g_appMsg).hide()
    });
    $("#cancel-btn").click(function(event) {
        event.stopPropagation();
        location.href = gotoUrl;
        return
    });
	$(document).on('mouseover', "#appmsgItem1,.sub-msg-item", function() {
        $(this).addClass("sub-msg-opr-show");
    });
    $(document).on('mouseout', "#appmsgItem1,.sub-msg-item", function() {
        $(this).removeClass("sub-msg-opr-show");
    });
    $(".sub-add-btn").click(function() {
        if ($(".sub-msg-item").size() >= 7) {
            Msg.error("最多只能加入8条图文信息");
            return
        }
        var $lastItem = $(".sub-msg-item:last");
        var $newItem = $lastItem.clone();
        $("input,textarea", $newItem).val("");
        $(".i-title", $newItem).text("标题");
        $(".thumb", $newItem).html('<span class="default-tip">缩略图</span><img src="" class="i-img" style="display:none">');
        $(".cover,.coverurl", $newItem).val("");
        $(".i-img", $newItem).hide();
        $(".rid", $newItem).val(0);
        $lastItem.after($newItem)
    });
   $(document).on("click", '.sub-msg-opr .edit-icon', function() {
		g_appMsg.find(".content").val(window.msg_editor.getContent());
		var $msgItem = $(this).closest(".appmsgItem");
		var index = $(".appmsgItem").index($msgItem);
		g_appMsgIndex = index;
		g_appMsg = $msgItem;
		$("#title").val($(".title", $msgItem).val());
		$("#author").val($(".author", $msgItem).val());
		if ($(".coverurl", $msgItem).val() == "") {
			$("#imgArea").hide()
		} else {
			$("#imgArea").show().find("#img").attr("src", $(".coverurl", $msgItem).val())
		}
		if ($(".sourceurl", $msgItem).val() == "") {
			$("#url-block-link").show();
			$("#url-block").hide().find("#source_url").val("")
		} else {
			$("#url-block-link").hide();
			$("#url-block").show().find("#source_url").val($(".sourceurl", $msgItem).val())
		}
		if($(".isCover", $msgItem).val() == "1"){
			$("#isCover").prop("checked",true);
		}else{
			$("#isCover").prop("checked",false);
		}
		window.msg_editor.setContent($(".content", $msgItem).val());
		if (index == 0) {
			$(".msg-editer-wrapper").css("margin-top", "0px")
		} else {
			var top = 110 + $(".sub-msg-item").eq(0).outerHeight(true) * index;
			$(".msg-editer-wrapper").css("margin-top", top + "px")
		}
		 return false;
	});
    $(document).on("click", ".sub-msg-opr .del-icon", function() {
		if ($(".appmsgItem").size() <= 2) {
			Msg.error("无法删除，多条图文至少需要2条消息。");
			return
		}
		if (confirm("确认删除此消息？")) {
			var $msgItem = $(this).closest(".sub-msg-item");
			var rid=$(".rid", $msgItem).val();
			if ($(".rid", $msgItem).size() > 0 && rid>0) {
				g_delResId.push(rid)
				console.log(g_delResId);
			}
			$msgItem.remove()
		}
		 return false;
	});
    g_appMsgIndex = 0;
    g_appMsg = $("#appmsgItem1");
    g_delResId = [];
    window.msg_editor = new UE.ui.Editor({
        initialFrameWidth: 498
    });
    window.msg_editor.render("editor");
    $("#save-btn").click(function() {
        var $btn = $(this);
        if ($btn.hasClass("disabled")) {
            return
        }
        g_appMsg.find(".content").val(window.msg_editor.getContent());
        var valid = true;
        var $msgItem;
        var jsonData = [];
        $(".appmsgItem").each(function(index, msgItem) {
            $msgItem = $(msgItem);
            var title = $("input.title", $msgItem).val();
            var cover = $("input.cover", $msgItem).val();
            var content = $("textarea.content", $msgItem).val();
            var sourceurl = $("input.sourceurl", $msgItem).val();
			var author = $("input.author", $msgItem).val();
			var isCover = $("input.isCover", $msgItem).val();
			var rid = $("input.rid", $msgItem).val();
            if (title == "") {
                Msg.error("标题不能为空");
                valid = false;
                return false
            }
           if (cover == "") {
                Msg.error("必须上传一个封面图片");
                valid = false;
                return false
            }
/*            if (content == "") {
                Msg.error("正文不能为空");
                valid = false;
                return false
            }*/
            jsonData[index] = {
                title: title,
				author:author,
                coverurl: cover,
				isCover:isCover,
				summary:'',
                content: content,
                source_url: sourceurl,
				rid:rid
            };
        });
        if (!valid) {
            $(".edit-icon", $msgItem).click();
            return false
        }
        var sumbitData = {
		  tid:$('#tid').val(),
		  id:$('#id').val(),
		  px:$('#px').val(),
		  data:jsonData,
		  act: $("#action").val(),
		  delIds:g_delResId
        };
       $btn.addClass("disabled");
		Msg.loading();
        $.post(postUrl, sumbitData,function(data) {
			Msg.hide();
			$btn.removeClass("disabled");
			if (data.status == 1) {
				location.href = gotoUrl;
			} else {
				Msg.error(data.info);
			}
		},
		"json");
	return false;
    })
});
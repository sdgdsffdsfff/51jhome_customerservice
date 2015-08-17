$(function() {
    var g_appMsg;
    var g_delResId;
    $("#file_upload").omFileUpload({
        action: postPicUrl,
        fileExt: "*.jpg;*.png;*.gif;*.jpeg;*.bmp",
        fileDesc: "Image Files",
        sizeLimit: 2 * 1024 * 1024,
        onError: function(ID, fileObj, errorObj, event) {
            if (errorObj.type == "File Size") {
                Msg.error("上传图片的大小不能拿超过2M")
            }
        },
        autoUpload: true,
        swf: PUBLIC_URL + "resource/swf/om-fileupload.swf",
        method: "POST",
        actionData: {
            uid: USER_ID,
			vcode:vcode
        },
        onComplete: function(ID, fileObj, response, data, event) {
            var jsonData = eval("(" + response + ")");
            if (jsonData.result == "error") {
                alert(jsonData.msg);
                return false
            }
            $(".default-tip", g_appMsg).hide();
            $(".i-img", g_appMsg).attr("src", jsonData.fileUrl).show();
            $("#imgArea").show().find(" #img").attr("src", jsonData.fileUrl);
            $(".cover", g_appMsg).val(jsonData.savePath);
            $(".coverurl", g_appMsg).val(jsonData.savePath)
        }
    });
    $(".msg-editer #title").bind("keyup",
            function() {
                $(".i-title", g_appMsg).text($(this).val());
                $(".title", g_appMsg).val($(this).val())
            });
    $(".msg-editer #title").blur(function() {
        $(".i-title", g_appMsg).text($(this).val());
        $(".title", g_appMsg).val($(this).val())
    });
    $("#source_url").bind("keyup", function() {
        $(".sourceurl", g_appMsg).val($(this).val())
    });
    $("#author").blur(function() {
        $(".author", g_appMsg).val($(this).val())
    });
	$("#isCover").blur(function() {
        $(".isCover", g_appMsg).val($(this).val())
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
    $("#appmsgItem1,.sub-msg-item").live({
        mouseover: function() {
            $(this).addClass("sub-msg-opr-show")
        },
        mouseout: function() {
            $(this).removeClass("sub-msg-opr-show")
        }
    });
    $(".sub-add-btn").click(function() {
        var len = $(".sub-msg-item").size();
        if (len >= 7) {
            Msg.error("最多只能加入8条图文信息");
            return
        }
        var $lastItem = $(".sub-msg-item:last");
        var $newItem = $lastItem.clone();
        $("input,textarea", $newItem).val("");
        $(".i-title", $newItem).text("");
        $(".default-tip", $newItem).css("display", "block");
        $(".cover,.coverurl", $newItem).val("");
        $(".i-img", $newItem).hide();
        $(".rid", $newItem).remove();
        $lastItem.after($newItem)
    });
    $(".sub-msg-opr .edit-icon").live("click",
            function() {
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
				if($(".isCover", $msgItem).val() == "1"){
					$("#isCover").attr("checked", true);
				}else{
					$("#isCover").attr("checked", false);
				}
                if ($(".sourceurl", $msgItem).val() == "") {
                    $("#url-block-link").show();
                    $("#url-block").hide().find("#source_url").val("")
                } else {
                    $("#url-block-link").hide();
                    $("#url-block").show().find("#source_url").val($(".sourceurl", $msgItem).val())
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
    $(".sub-msg-opr .del-icon").live("click",
            function() {
                var len = $(".appmsgItem").size();
                if (len <= 2) {
                    Msg.error("无法删除，多条图文至少需要2条消息。");
                    return
                }
                if (confirm("确认删除此消息？")) {
                    var $msgItem = $(this).closest(".sub-msg-item");
                    if ($(".rid", $msgItem).size() > 0) {
                        g_delResId.push($(".rid", $msgItem).val())
                    }
                    $msgItem.remove();
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
            if (title == "") {
                Msg.error("标题不能为空");
                valid = false;
                return false
            }
/*            if (cover == "") {
                alert("必须上传一个封面图片");
                valid = false;
                return false
            }*/
/*            if (content == "") {
                alert("正文不能为空");
                valid = false;
                return false
            }*/
            jsonData[index] = {
                title: title,
                cover: cover,
                content: content,
                sourceurl: sourceurl,
				isCover: isCover,
				author: author
            };
        });
        if (!valid) {
            $(".edit-icon", $msgItem).click();
            return false
        }
        var sumbitData = {
			tid:$('#tid').val(),
            type: 2,
            data: jsonData,
            action: $("#action").val()
        };
        $btn.addClass("disabled");
		Msg.loading();
        $.post(postUrl, sumbitData,
                function(data) {
					Msg.hide();
                    $btn.removeClass("disabled");
                    if (data.status == 1) {
                        location.href = gotoUrl
                    } else {
                        Msg.error(data.info);
                    }
                },
                "json")
    })
});
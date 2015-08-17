//身边事专用编辑器上传
$(function() {
    $("#file_upload").omFileUpload({
        action: postPicUrl,
        fileExt: "*.jpg;*.png;*.gif;*.jpeg;*.bmp",
        fileDesc: "Image Files",
        sizeLimit: 2 * 1024 * 1024,
        onError: function(ID, fileObj, errorObj, event) {
            if (errorObj.type == "File Size") {
                Msg.error("\u4e0a\u4f20\u56fe\u7247\u7684\u5927\u5c0f\u4e0d\u80fd\u8d85\u8fc7\u0032\u004d")
            }
        },
        autoUpload: true,
        swf: UEDITOR_HOME_URL+"swf/om-fileupload.swf",
        method: "POST",
        actionData: {},
        onComplete: function(ID, fileObj, response, data, event) {
            var jsonData = eval("(" + response + ")");
            if (jsonData.result == "error") {
                Msg.error(jsonData.msg);
                return false
            }
            $(".cover .i-img").attr("src", jsonData.fileUrl).show();
            $("#imgArea").show().find("#img").attr("src", jsonData.fileUrl);
            $("#coverurl").val(jsonData.savePath);
            $(".default-tip").hide()
        }
    });
    $(".msg-editer #title").bind("keyup", function() {
        $(".i-title").text($(this).val())
    });
    $(".msg-editer #summary").bind("keyup", function() {
        $(".msg-text").text($(this).val())
    });
    $("#desc-block-link").click(function() {
        $("#desc-block").show();
        $(this).hide()
    });
    $("#url-block-link").click(function() {
        $("#url-block").show();
        $(this).hide()
    });
	$("#isCover").click(function(){
		if($(this).is(":checked")){
			$(this).val(1);
		}else{
			$(this).val(0);
		}
	});
    $("#delImg").click(function() {
		$("#isCover").attr("checked",false);
        $(".default-tip").show();
        $("#imgArea").hide();
        $("#coverurl").val("");
        $(".cover .i-img").hide()
    });
    $("#cancel-btn").click(function(event) {
        event.stopPropagation();
        location.href = gotoUrl;
        return
    });
   $("#appmsg-form").submit(function(){
	  var $form = $(this);
	  var $btn = $("#save-btn");
	  if ($btn.hasClass("disabled")) {
		  return false;
	  }
	  var actionData=new Array();
	  var editUplodeImage = [];
	  for(var i=0;i<$("input[name='editUplodeImage']").length;i++){
		  editUplodeImage.push($("input[name='editUplodeImage']").eq(i).val());		  
	  }
	  actionData[0]={
		  title: $("input[name='title']", $form).val(),
		  author: $("input[name='author']", $form).val(),
		  coverurl: $("input[name='coverurl']", $form).val(),
		  isCover: $("input[name='isCover']", $form).val(),
		  summary: $("textarea[name='summary']", $form).val(),
		  tagid: $("select[name='tagid']", $form).val(),
		  editUplodeImage: editUplodeImage,
		  content:window.msg_editor.getContent(),
		  source_url: $("#source_url", $form).val(),
	  }
	  var submitData = {      
		  tid:$('#tid').val(),
		  id:$('#id').val(),
		  px:$('#px').val(),
		  data:actionData,
		  act: $("#action", $form).val()
	  };
	  if (!actionData[0].title) {
		  Msg.error("\u6807\u9898\u4e0d\u80fd\u4e3a\u7a7a");   
		  return false
	  }
	  var editorContent = window.msg_editor.getContent();
	  if (editorContent > 20000) {
		  Msg.error("\u6b63\u6587\u7684\u5185\u5bb9\u4e0d\u80fd\u8d85\u8fc7\u0032\u0030\u0030\u0030\u0030\u4e2a\u5b57");
		  msg_editor.focus();
		  return false
	  }
	  $btn.addClass("disabled");
	  Msg.loading();
	  $.post(postUrl, submitData,function(data) {
		  $btn.removeClass("disabled");
		  Msg.hide();
		  if (data.status == 1) {
			 location.href = gotoUrl;
		  } else {
			   Msg.error(data.info);
			   return false;
		  }
	  //});
	  },"json");
	  return false; 
	});
    window.msg_editor = new UE.ui.Editor({
        initialFrameWidth: 498
    });
    window.msg_editor.render("editor")
});
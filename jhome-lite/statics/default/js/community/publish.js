$(function(){
    var enabledSmiley = '1';//1|25
	var maxUploadCounts=8;//照片最大上传数量
	var defaultImgUrl='images/defaultImg.png';//上传默认背景图标
    var uploadObj={
        maxUpload: maxUploadCounts,
        uploadInfo: {},
        uploadQueue: [],
        previewQueue: [],
        isBusy: false,
        isUploading:null,
        getgps:0,
        xhr: {},
        countUpload: function() {
            var num = 0;
            $.each(uploadObj.uploadInfo, 
            function(i, n) {
                if (n) {
                    ++num;
                }
            });
            return num;
        },
        checkIsUploading:function(){
            uploadObj.isUploading=setInterval(function() {
                setTimeout(function() {
                    if (uploadObj.previewQueue.length) {
                        var jobId = uploadObj.previewQueue.shift();
                        uploadObj.uploadPreview(jobId);
                    }
                },
                1);
                setTimeout(function() {
                    if (!uploadObj.isBusy && uploadObj.uploadQueue.length) {
                        var jobId = uploadObj.uploadQueue.shift();
                        uploadObj.isBusy = true;
                        uploadObj.createUpload(jobId);
                    }
                },
                10);
            },
            300);
        },
        clearIsUploading:function (){
            clearInterval(uploadObj.isUploading);
        },
        uploadPreview: function(id) {
           // console.log('开始');
            var reader = new FileReader();
            var uploadBase64;
            var conf = {},
            file = uploadObj.uploadInfo[id].file;
            if (window.NETTYPE == window.NETTYPE_WIFI) {
                conf = {
                    maxW: 720,
                    maxH: 1000,
                    quality: 0.8,
                };
            }else{
                conf = {
                    maxW: 640,
                    maxH: 1000,
                    quality: 0.8,
                };  
            }
            reader.onload = function(e) {
                var result = this.result;
                if (file.type == 'image/jpeg') {
                    try {
                        var jpg = new JpegMeta.JpegFile(result, file.name);
                    } catch(e) {
                        $.DIC.dialog({
                            content: '图片不是正确的图片数据',
                            autoClose: true
                        });
                        $('#li' + id).remove();
                        return false;
                    }
                    if (jpg.tiff && jpg.tiff.Orientation) {
                        conf = $.extend(conf, {
                            orien: jpg.tiff.Orientation.value
                        });
                    }
                }
                if (ImageCompresser.support()) {
                   // console.log('压缩了')
                    var img = new Image();
                    img.onload = function() {
                        //console.log(conf);
                        try {
                            uploadBase64 = ImageCompresser.getImageBase64(this, conf);
                        } catch(e) {
                            //alert(e)
                            $.DIC.dialog({
                                content: '压缩图片失败',
                                autoClose: true
                            });
                            $('#li' + id).remove();
                            return false;
                        }
                        if (uploadBase64.indexOf('data:image') < 0) {
                            $.DIC.dialog({
                                content: '上传照片格式不支持',
                                autoClose: true
                            });
                            $('#li' + id).remove();
                            return false;
                        }
                        uploadObj.uploadInfo[id].file = uploadBase64;
                        $('#li' + id).find('img').attr('src', uploadBase64);
                        uploadObj.uploadQueue.push(id);
                    }
                    img.onerror = function() {
                        $.DIC.dialog({
                            content: '解析图片数据失败',
                            autoClose: true
                        });
                        $('#li' + id).remove();
                        return false;
                    }
                    img.src = ImageCompresser.getFileObjectURL(file);
                } else {
                    //console.log('没压缩')
                    uploadBase64 = result;
                    if (uploadBase64.indexOf('data:image') < 0) {
                        $.DIC.dialog({
                            content: '上传照片格式不支持',
                            autoClose: true
                        });
                        $('#li' + id).remove();
                        return false;
                    }
                    uploadObj.uploadInfo[id].file = uploadBase64;
                    $('#li' + id).find('img').attr('src', uploadBase64);
                    uploadObj.uploadQueue.push(id);
                }
            }
            //console.log('输出：')
            //console.log(uploadObj.uploadInfo[id].file)
            reader.readAsBinaryString(uploadObj.uploadInfo[id].file);
            //uploadObj.createUpload(id);
        },
        createUpload: function(id) {
           // alert(id);
            if (!uploadObj.uploadInfo[id]) {
                return false;
            }
            //压缩上传的路径
            var uploadUrl =  'upload.php?act=upload';
			var uploadUrl =  'http://192.168.1.203/hzforall/ihome/weixin/upload/save.html?s=qun';
            //上传提示层
            var progressHtml = '<div class="progress" id="progress' + id + '"><div class="proBar" style="width:0%;"></div></div>';
            $('#li' + id).find('.maskLay').after(progressHtml);
            var progress = function(e) {
                if (e.target.response) {
                    var result = $.parseJSON(e.target.response);
                    if (result.status != 1) {
                        $.DIC.dialog({
                            content: '网络不稳定，请稍后重新操作',
                            autoClose: true
                        });
                        removePic(id);
                    }
                }
                var progress = $('#progress' + id).find('.proBar');
                if (e.total == e.loaded) {
                    var percent = 100;
                } else {
                    var percent = 100 * (e.loaded / e.total);
                }
                if (percent > 100) {
                    percent = 100;
                }
                progress.css('width', percent + '%');
                if (percent == 100) {
                    $('#li' + id).find('.maskLay').remove();
                    $('#li' + id).find('.progress').remove();
                }
            }
            var removePic = function(id) {
                donePic(id);
                $('#li' + id).remove();
            }
            var donePic = function(id) {
                uploadObj.isBusy = false;
                if (typeof uploadObj.uploadInfo[id] != 'undefined') {
                    uploadObj.uploadInfo[id].isDone = true;
                }
                if (typeof uploadObj.xhr[id] != 'undefined') {
                    uploadObj.xhr[id] = null;
                }
            }
            var complete = function(e) {
                var progress = $('#progress' + id).find('.proBar');
                progress.css('width', '100%');
                $('#li' + id).find('.maskLay').remove();
                $('#li' + id).find('.progress').remove();
                donePic(id);
                var result = $.parseJSON(e.target.response);
                if (result.status==1) {
                    var input = '<input type="hidden" id="input' + result.data.id + '" name="picIds[]" value="' + result.data.picId + '">';
                    $('#newthread').append(input);
                } else {
                    $.DIC.dialog({
                        content: '网络不稳定，请稍后重新操作',
                        autoClose: true
                    });
                    removePic(id);
                }
            }
            var failed = function() {
                $.DIC.dialog({
                    content: '网络断开，请稍后重新操作',
                    autoClose: true
                });
                removePic(id)
            }
            var abort = function() {
                $.DIC.dialog({
                    content: '上传已取消',
                    autoClose: true
                });
                removePic(id)
            }
            var formData = new FormData();
            formData.append('pic', uploadObj.uploadInfo[id].file);
            uploadObj.xhr[id] = new XMLHttpRequest();
            uploadObj.xhr[id].addEventListener("progress", progress, false);
            uploadObj.xhr[id].addEventListener("load", complete, false);
            uploadObj.xhr[id].addEventListener("abort", abort, false);
            uploadObj.xhr[id].addEventListener("error", failed, false);
            uploadObj.xhr[id].open("POST", uploadUrl + '&t=' + Date.now());
            uploadObj.xhr[id].send(formData);
           // $.post('./',{"data":formData},function(){},'json');
        },
        checkUploadBySysVer: function() {
            if (jQuery.os.android && (jQuery.os.version.toString().indexOf('4.4') === 0 || jQuery.os.version.toString() <= '2.1')) {
                $.DIC.dialog({
                    'content': '您的手机系统暂不支持传图',
                    'autoClose': true
                });
                return false;
            } else if (jQuery.os.ios && jQuery.os.version.toString() < '6.0') {
                $.DIC.dialog({
                    'content': '手机系统不支持传图，请升级到ios6.0以上',
                    'autoClose': true
                });
                return false;
            }
            if (jQuery.os.wx && jQuery.os.wxVersion.toString() < '5.2') {
                $.DIC.dialog({
                    'content': '当前微信版本不支持传图，请升级到最新版',
                    'autoClose': true
                });
                return false;
            }
            return true;
        },
        checkForm: function() {
            $.each(uploadObj.uploadInfo, 
            function(i, n) {
                if (n && !n.isDone) {
                    $.DIC.dialog({
                        content: '图片上传中，请等待',
                        autoClose: true
                    });
                    return false;
                }
            });
            var contentLen = $.DIC.mb_strlen($.DIC.trim($('#content').val()));
            if (contentLen < 15) {
                $.DIC.dialog({
                    content: '帖子内容过短',
                    autoClose: true
                });
                return false;
            }
            return true;
        },
        checkPicSize: function(file) {
            if (file.size > 10000000) {
                return false;
            }
            return true;
        },
        checkPicType: function(file) {
            return true;
        },
        initUpload: function() {
            $('#addPic').on('click', 
            function() {
                uploadObj.checkUploadBySysVer();
            });
            $('#adduploadField').on('click', 
            function() {
                if (uploadObj.isBusy) {
                    $.DIC.dialog({
                        content: '上传中，请稍后添加',
                        autoClose: true
                    });
                    return false;
                }
            });
            $('body').on('change', '#adduploadField', 
            function(e) {
                e = e || window.event;
                var fileList = e.target.files;
                if (!fileList.length) {
                    return false;
                }
                for (var i = 0; i < fileList.length; i++) {
                    if (uploadObj.countUpload() >= uploadObj.maxUpload) {
                        $.DIC.dialog({
                            content: '你最多只能上传8张照片',
                            autoClose: true
                        });
                        break;
                    }
                    var file = fileList[i];
                    if (!uploadObj.checkPicSize(file)) {
                        $.DIC.dialog({
                            content: '图片体积过大',
                            autoClose: true
                        });
                        continue;
                    }
                    if (!uploadObj.checkPicType(file)) {
                        $.DIC.dialog({
                            content: '上传照片格式不支持',
                            autoClose: true
                        });
                        continue;
                    }
                    var id = Date.now() + i;
                    uploadObj.uploadInfo[id] = {
                        file: file,
                        isDone: false,

                    };
                    var html = '<li id="li' + id + '"><div class="photoCut"><img src="'+defaultImgUrl+'" class="attchImg" alt="photo"></div>' + '<div class="maskLay"></div>' + '<a href="javascript:;" class="cBtn spr db " title="" _id="' + id + '">关闭</a></li>';
                    $('#addPic').before(html);
                    //console.log('加入上传队列');
                   // uploadObj.uploadPreview(id);
                    uploadObj.previewQueue.push(id);
                    if (!uploadObj.isUploading){
                        uploadObj.checkIsUploading();   
                    }
                }
                if (uploadObj.countUpload() >= uploadObj.maxUpload) {
                    $('#addPic').hide();
                }
                $(this).val('');
            });
            
            $('.photoList').on('click', '.cBtn', 
                function() {
                    var id = $(this).attr('_id');
                    if (uploadObj.xhr[id]) {
                        uploadObj.xhr[id].abort();
                    }
                    $('#li' + id).remove();
                    $('#input' + id).remove();
                    uploadObj.uploadInfo[id] = null;
                    if (uploadObj.countUpload() < uploadObj.maxUpload) {
                        $('#addPic').show();
                    }
            });
            
            $(".locationCon").on('click', 
            function() {
                if (uploadObj.getgps == 1 || uploadObj.getgps == 2) {
                    uploadObj.getgps = 0;
                    $('.locationCon').removeClass('curOn').html('<i class="locInco commF">' + '所有城市');
                    $('#LBSInfoLatitude').val('');
                    $('#LBSInfoLongitude').val('');
                    $('#LBSInfoProvince').val('');
                    $('#LBSInfoCity').val('');
                    $('#LBSInfoStreet').val('');
                    $('#cityCode').val('');
                } else if (uploadObj.getgps == 0) {
                    uploadObj.getgps = 1;
                    $('.locationCon').html('<i class="locInco commF">' + '正在定位...');
                   getGpsInfo.init();
                }
            });
        }
    };
    getGpsInfo.init();
    uploadObj.initUpload();
    smileyObj.init();
    //表情
    $(".expreSelect").on("click", 
    function() {
        if ($('.expreList').css('display') != 'none') {
            smileyObj.hide();
        } else {
            smileyObj.show();
        }
    });
    $(".photoSelect").on("click", smileyObj.hide);
	timer = setInterval(function() {
		$.DIC.strLenCalc($('textarea[name="content"]')[0], 'pText', 1000);
	},500);
    //发布
	var isSubmitButtonClicked = false;
	$('#submitButton').on('click', 
	function() {
		if (isSubmitButtonClicked || !uploadObj.checkForm()) {
			return false;
		}
		var opt = {
			success: function(re) {
				if (re.status == 1) {
					//发布成功
				} else {
					//发布失败
					isSubmitButtonClicked = false;
				}
			},
			error: function(re) {
				isSubmitButtonClicked = false;
			}
		};
		isSubmitButtonClicked = true;
		//提交数据========
		
		console.log('发布内容...');
		//提交数据========
		return false;
	});
	$('.cancelBtn').on('click', 
	function() {
		if ($('.photoList .attchImg').length > 0) {
			$.DIC.dialog({
				content: '是否放弃当前内容?<br/><span style="font-size:12px">上传的图片将丢失</span>',
				okValue: '确定',
				cancelValue: '取消',
				isMask: true,
				ok: function() {
					history.go( - 1);
				}
			});
		} else {
			history.go( - 1);
		}
		return false;
	});
	$('#content').on('focus', 
	function() {
		$('.bNav').hide();
	}).on('blur', 
	function() {
		$('.bNav').show();
	});
})
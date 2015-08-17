<?php
if (!defined('IN_XLP')) {
    exit('Access Denied');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>upload</title>
<style type="text/css">
<!--
* {
	margin:0;
	padding:0;
	background:#fff;
	font:12px Verdana;
}
.input {
	width:200px;
	height:22px;
}
.button {
	width:50px;
	border:1px solid #718da6;
	height:22px;
}
-->
</style>
<!--[if IE]>
<style type="text/css">
.input{border:1px solid #718da6;}
</style>
<![endif]-->
</head>
<body>
<form action="<?php echo U('upload/up');?>" method="post" enctype="multipart/form-data" name="upform" onSubmit="return checkform();">
  <input name="upimg" id="file" type="file" class="input"/>
  <input name="Submit" type="submit" class="button" value="上 传" />
  <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
  <input type="hidden" name="ids" id="ids" value="<?php echo $ids; ?>">
  <input type="hidden" name="path" id="path" value="<?php echo $dir; ?>">
</form>
<script language="javascript">
    function $(sID) {
        return document.getElementById(sID);
    }
    var allow_file_type='<?php echo $upload_img_type; ?>';
    var field = $("file");
    function getFileExtension(filePath) { //v1.0
        fileName = ((filePath.indexOf('/') > -1) ? filePath.substring(filePath.lastIndexOf('/')+1,filePath.length) : filePath.substring(filePath.lastIndexOf('\\')+1,filePath.length));
        return fileName.substring(fileName.lastIndexOf('.')+1,fileName.length);
    }
    function checkFileUpload(extensions) { //v1.0
        if (extensions.toLowerCase().indexOf(getFileExtension(field.value).toLowerCase()) == -1) {
            alert('这种文件类型不允许上传!');
            field.focus();
            return false;
        }
        return true;
    }
    function checkform(){
        if (field.value == '') {
            alert('文件框中必须保证已经有文件被选中!');
            field.focus();
            return false;
        }
        if (allow_file_type){
            return checkFileUpload(allow_file_type);
        }
    }
</script>
</body>
</html>
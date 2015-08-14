<?php
$mime = array(
  'jpeg' => 'image/jpeg',
  'jpg' => 'image/jpeg',
  'jpe' => 'image/jpeg',
  'pdf' => 'application/pdf',
  'png' => 'image/png',
  'bmp' => 'image/bmp',
  'git' => 'image/gif',
  'tiff' => 'image/tiff',
  'tif' => 'image/tiff',
  'zip' => 'application/zip',
  'gz' => 'application/x-gzip',
  'tgz' => 'application/x-gzip',
  'bz' => 'application/x-bzip2',
  'bz2' => 'application/x-bzip2',
  'tbz' => 'application/x-bzip2',
  'zip' => 'application/zip',
  'rar' => 'application/x-rar',
  'tar' => 'application/x-tar',
  '7z' => 'application/x-7z-compressed',
  'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'doc' => 'application/msword',
  'ppt' => 'application/vnd.ms-powerpoint',
  'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
  'xls' => 'application/vnd.ms-excel',
  'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
);
$allow_type = 'jpeg|jpe|jpg|gif|png|bmp';
$maxSize = 200000; // kb
$uploadPath = 'upload';
$allow_mime = array_intersect(array_flip($mime), explode('|',chop($allow_type, '|')));
$uploadFile = $_FILES['file'];
$fileType = $uploadFile['type'];
$fileSize = $uploadFile['size'];
$callback = $_POST['callback'];
$opt = $_POST['opt'] || '';
function printScript($callback, $opt, $status, $message, $img = ''){
    $json = array(
        'status' => $status,
        'message' => $message,
        'data' => array('opt' => $opt, 'img' => $img)
    );
    echo '<script type="text/javascript">parent.window.'.$callback.'(\''.json_encode($json).'\');</script>';
}
if(!empty($allow_mime[$fileType])){
    if($fileSize > $maxSize){
        printScript($callback, $opt, -2, '上传的文件超过限制');
        return;
    }
    if($uploadFile['error'] > 0){
        printScript($callback, $opt, -3, 'Return Code:'.$uploadFile['error']);
        return;
    }
    $new_name = time(). '.'. pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $fullPath = $uploadPath.'/'.$new_name;
    if(file_exists($fullPath)){
        printScript($callback, $opt, -4, '文件已经存在');
        return;
    }
    move_uploaded_file($uploadFile['tmp_name'], $fullPath);
    $img_info = array(
        'filename' => $_FILES["file"]["name"],
        'size' => $_FILES['file']['size'],
        'type' => $_FILES['file']['type'],
        'fullPath' => $fullPath);
    printScript($callback, $opt, 0, '上传成功', $img_info);
    return;
}else{
    printScript($callback, $opt, -1, '该文件类型不允许上传');
}

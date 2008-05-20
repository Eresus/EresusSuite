<?php
/**
* Создание миниатюр
*
* Создаёт миниатюру
*
* @author: Mikhail Krasilnikov <mk@procreat.ru>
* @version: 0.0.1
* @modified: 2007-07-26
*
* @param  string   file    Имя исходного файла относительно /data
* @param  integer  width   Ширина миниатюры в пикселях
* @param  integer  height  Высота миниатюры в пикселях
*/

define('root', realpath(dirname(__FILE__).'/../../data').DIRECTORY_SEPARATOR);
$file = $_REQUEST['file'];
$file = realpath(root.$file);
if ($file) $file = realpath(root.substr($file, strlen(root)));
if ($file) {
  $info = getimagesize($file);
  switch ($info[2]) {
    case IMG_GIF : $img = imageCreateFromGIF($file); break;
    case IMG_JPG :
    case IMG_JPEG: $img = imageCreateFromJPEG($file); break;
    case IMG_PNG : $img = imageCreateFromPNG($file); break;
  }
  if ($img) {
    $width = isset($_REQUEST['width']) ? $_REQUEST['width'] : $info[0];
    $height = isset($_REQUEST['height']) ? $_REQUEST['height'] : $info[1];
    $thumb = imageCreateTrueColor($width, $height);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
    header('Content-type: image/jpeg');
    imageJPEG($thumb);
  } else header('Unsupported Media Type', true, 415);
} else header('Not found', true, 404);
?>
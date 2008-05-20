<?php
/**
 * Библиотека для работы с изображениями
 *
 * Система управления контентом Eresus™ 2
 * © 2007, Eresus Group, http://eresus.ru/
 *
 * @author Mikhail Krasilnikov <mk@procreat.ru>
 * @version 0.0.3
 */

/**
 * Создание изображения из файла
 *
 * @param string $filename Имя файла
 * @return mixed Дескриптор или false
 */
function imageCreateFromFile($filename)
{
	$type = getimagesize($filename);
	switch ($type[2]) {
		case IMG_GIF:  $result = imageCreateFromGIF($filename); break;
		case IMG_JPG:
		case IMG_JPEG: $result = imageCreateFromJPEG($filename); break;
		case IMG_PNG:  $result = imageCreateFromPNG($filename); break;
		case IMG_WBMP: $result = imageCreateFromWBMP($filename); break;
		case IMG_XPM:  $result = imageCreateFromXPM($filename); break;
		default:
			switch(substr($type['mime'], 6)) {
				case 'gif':  $result = imageCreateFromGIF($filename); break;
				case 'jpeg': $result = imageCreateFromJPEG($filename); break;
				case 'png':  $result = imageCreateFromPNG($filename); break;
				case 'wbmp': $result = imageCreateFromWBMP($filename); break;
				case 'xpm':  $result = imageCreateFromXPM($filename); break;
				default:       $result = false;
			}
	}
	return $result;
}

/**
 * Сохранение изображения в файл заданного формата
 *
 * @param resource  $image     Изображение
 * @param string    $filename  Имя файла
 * @param int       $format		 Формат файла
 * @return mixed Дескриптор или false
 */
function imageSaveToFile($image, $filename, $format)
{
	$result = false;
	switch ($format) {
		case IMG_GIF:
			$result = imageGIF($image, $filename);
		break;
		case IMG_JPG:
		case IMG_JPEG:
			$quality = func_num_args() > 3 ? func_get_arg(3) : 80;
			$result = imageJPEG($image, $filename, $quality);
		break;
		case IMG_PNG:
			$quality = func_num_args() > 3 ? func_get_arg(3) : 7;
			$filters = func_num_args() > 4 ? func_get_arg(4) : 0;
			$result = imagePNG($image, $filename, $quality, $filters);
		break;
		case IMG_WBMP:
			$foreground = func_num_args() > 3 ? func_get_arg(3) : null;
			$result = imageWBMP($image, $filename, $foreground);
		break;
	}
	return $result;
}


/**
 * Создание миниатюры
 *
 * @param string $srcFile  Исходный файл
 * @param string $dstFile  Файл миниатюры
 * @param int    $width    Ширина миниатюры
 * @param int    $height   Высота миниатюры
 * @param mixed  $fill     Заполнение фона
 *
 * @return bool Результат
 */
function thumbnail($srcFile, $dstFile, $width, $height, $fill = null, $quality = 80)
{
	$result = false;
	$src = imageCreateFromFile($srcFile);
	if ($src) {
		$sW = imageSX($src);
		$sH = imageSY($src);
		$resizer = ($sW/$width > $sH/$height) ? ($sW / $width) : ($sH / $height);
		$dW = floor($sW / $resizer);
		$dH = floor($sH / $resizer);
		if (is_null($fill)) {
			$dst = imageCreateTrueColor($dW, $dH);
			imageCopyResampled($dst, $src, 0, 0, 0, 0, $dW, $dH, $sW, $sH);
		} else {
			$dst = imageCreateTrueColor($width, $height);
			if ($fill[0] == '#') {
				$R = hexdec(substr($fill, 1, 2));
				$G = hexdec(substr($fill, 3, 2));
				$B = hexdec(substr($fill, 5, 2));
			} else {
				$fill = explode(',', $fill);
				$R = trim($fill[0]);
				$G = trim($fill[1]);
				$B = trim($fill[2]);
			}
		imagefill($dst, 0, 0, imagecolorallocate($dst, $R, $G, $B));
		imageCopyResampled($dst, $src, round(($width-$dW)/2), round(($height-$dH)/2), 0, 0, $dW, $dH, $sW, $sH);
		}
		$result = ImageJPEG($dst, $dstFile, $quality);
		ImageDestroy($src);
		ImageDestroy($dst);
	}
	return $result;
}

/**
 * Изменение формата изображения
 *
 * @param string  $srcFilename  Имя исходного файла
 * @param string  $dstFilename  Имя файла назначения
 * @param int     $format       Формат файла назначения
 * @return bool  Результат выполнения
 */
function imageConvert($srcFile, $dstFile, $format = IMG_JPG)
{
	$src = imageCreateFromFile($srcFile);
	if ($src) $result = imageSaveToFile($src, $dstFile, $format);
	else $result = false;
	return $result;
}


?>
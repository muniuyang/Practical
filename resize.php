<?php 

/*
 * 摘	要： 裁图
 * 作	者： 王允
 */
 
ini_set("display_errors", "On");
error_reporting(E_ALL);

$BASE_PATH = "/data/apache/htdocs/img_5igupiao";

function resizeimage($maxwidth, $maxheight, $newsize, $is_cut)
{
	global $oldfile, $type, $filename, $filepath, $BASE_PATH;

	$im = '';
	if($type == '1')
	{
		$im = imagecreatefromgif($oldfile);
	}
	else if($type == '2')
	{
		$im = imagecreatefromjpeg($oldfile);
	}
	else if($type == '3')
	{
		$im = imagecreatefrompng($oldfile);
	}

	$old_width = imagesx($im);
	$old_height = imagesy($im);

	$ratio = 1;
	$off_x = 0;
	$off_y = 0;

	if(file_exists($BASE_PATH.$filepath) == false)
	{
		mkdir($BASE_PATH.$filepath);
	}

	if($maxwidth > 0 || $maxheight > 0 || $newsize > 0) 
	{
		if($maxwidth > 0 && $maxheight > 0) 
		{
			//$ratio = 1;
			$newwidth = $maxwidth;
			$newheight = $maxheight;
		}
		else if($maxwidth > 0 || $maxheight > 0)
		{
			//$maxwidth != "" ? $ration = $maxwidth/$old_width : $ration = $maxheight/$old_height;
			if($maxwidth != ""){
				$ratio = $maxwidth/$old_width;
			}else{
				$ratio = $maxheight/$old_height;
			}

			$newwidth = $old_width * $ratio;
			$newheight = $old_height * $ratio;
		}
		else if($newsize > 0) 
		{			
			$size = explode("*", $newsize);

			if($is_cut == 0) 
			{
				$ratio = $size[0]/$old_width < $size[1]/$old_height ? $size[0]/$old_width : $size[1]/$old_height;	//缩放最大比率
				$newwidth = $old_width * $ratio;
				$newheight = $old_height * $ratio;				
			}
			else if($is_cut == 1 || $is_cut == 2) 
			{
				$off_x_bool = false;
				$off_y_bool = false;
				//$ratio = $size[0]/$old_width < $size[1]/$old_height ? $size[1]/$old_height : $size[0]/$old_width;	//缩放最小比率
				if($size[0]/$old_width < $size[1]/$old_height) 
				{
					$ratio = $size[1]/$old_height;
					$off_x_bool = true;
				}
				else
				{
					$ratio = $size[0]/$old_width;
					$off_y_bool = true;					
				}

				$tmp_width = $old_width;
				$tmp_height = $old_height;
				$old_width = $size[0] / $ratio;
				$old_height = $size[1] / $ratio;
				$newwidth = $size[0];
				$newheight = $size[1];

				if($is_cut == 2) 
				{
					if($off_x_bool) 
					{
						$off_x = ($tmp_width-$old_width)/2;
					}

					if($off_y_bool) 
					{
						$off_y = ($tmp_height-$old_height)/2;
					}
				}
			}
		}

		$newim = imagecreatetruecolor($newwidth, $newheight);
		//$newim = imagecreate($newwidth, $newheight);

		imagecopyresampled($newim, $im, 0, 0, $off_x, $off_y, $newwidth, $newheight, $old_width, $old_height);

		if($type == '1')
		{
			imagegif($newim, "$BASE_PATH/$filepath/$filename");
		}
		else if($type == '2')
		{
			imagejpeg($newim, "$BASE_PATH/$filepath/$filename");
		}
		else if($type == '3')
		{
			imagepng($newim, "$BASE_PATH/$filepath/$filename");
		}
		
		imagedestroy ($newim);
	}
	else
	{
		if($type == '1')
		{
			imagegif($im, "$BASE_PATH/$filepath/$filename");
		}
		else if($type == '2')
		{
			imagejpeg($im, "$BASE_PATH/$filepath/$filename");
		}
		else if($type == '3')
		{
			imagepng($im, "$BASE_PATH/$filepath/$filename");
		}
	}

	imagedestroy($im);

}

function showimage()
{
	global $p, $filename, $filepath, $BASE_PATH;

	if(stripos($p, ".gif") !== false)
	{
		header("Content-type: image/gif");
	}
	else if(stripos($p, ".jpeg") !== false || stripos($p, ".jpg") !== false || stripos($p, ".jpe") !== false)
	{
		header("Content-type: image/jpeg");
	}
	else if(stripos($p, ".png") !== false)
	{
		header("Content-type: image/png");
	}

	$img_file = fopen("$BASE_PATH/$filepath/$filename", "r");
	echo fread($img_file, filesize("$BASE_PATH/$filepath/$filename"));
	fclose($img_file);

	die();
}

$p = trim($_GET["p"]);	//源图片url
$w = intval(trim($_GET["w"]));	//生成图片的宽
$h = intval(trim($_GET["h"]));	//生成图片的高
$s = trim($_GET["s"]);	//生成图片要适应的规格（如90*60，按缩放比例最大一边进行等比例缩放）
$c = intval(trim($_GET["c"]));	//是否支持裁图（0:否 1:是，从左上角裁 2:是，从中间裁）

/*if($_SERVER["HTTP_REFERER"] != "") 
{
	if(strpos($_SERVER["HTTP_REFERER"], ".5igupiao.com") === false) 
	{
		die("must from .5igupiao.com");
	}
}*/

if($p == "") 
{
	die("wrong pic path");
}
if(strpos($p, "http://img.5igupiao.com") !== false)
{
	$p = str_replace("http://img.5igupiao.com", "", $p);
}
else if(strpos($p, "http://cdn.5igupiao.com") !== false)
{
    $BASE_PATH = "/data/apache/htdocs/static.5igupiao.com";
	echo $p = str_replace("http://cdn.5igupiao.com", "", $p);
}

 
$oldfile = realpath("$BASE_PATH/$p");
 var_dump($oldfile);
if(strpos($oldfile, $BASE_PATH) === false)
{
	die("path not support");
}

$filename = md5($_SERVER["QUERY_STRING"]);
$filepath = "/resize_file/".hexdec(substr($filename, -4))%1000;

if(file_exists("$BASE_PATH/$filepath/$filename") == true)
{
	showimage();
}

$size = getimagesize($oldfile);
$width = $size[0];		//源图片宽度
$height = $size[1];		//源图片高度
$type = $size[2];		//源图片MIME类型
if($type != "1" && $type != "2" && $type != "3")
{
	die("type not support");
}

if($width != 0 && $height != 0) 
{
	resizeimage($w, $h, $s, $c);

	showimage();
}
?>

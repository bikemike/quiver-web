<?php
require_once ("functions.php");


$image = stripslashes($_GET['image']);
$dir = stripslashes($_GET['dir']);
$w = $_GET['w'];
$h = $_GET['h'];
$rotate = $_GET['rotate'];
$cmd = $_GET['cmd'];
$type = $_GET['type'];

$img_file = addPaths($picture_dir,addPaths($dir, $image));

$thumbdir = addPaths($thumbnail_dir,$dir);
$smalldir = addPaths($small_dir,$dir);

$thumb_file = addPaths($thumbdir,$image);
$small_file = addPaths($smalldir,$image);

$tmp_file = "/tmp/exif_img_" . randomNum(10);
$tmp_file_rotated = "/tmp/exif_img_" . randomNum(10);
if ($type == "thumbnail")
{
	$thumb=@exif_thumbnail($img_file,$wid,$hei,$tp);
	if ($thumb)
	{
		$fhand = @fopen($tmp_file,"w+");
		if ($fhand)
		{
			fwrite($fhand,$thumb);
			fclose($fhand);
			$img_file = $tmp_file;
		}
	}

	// Rotate portrait thumbnails using jpegtran
	// because they turn out really blurry with imlib
	if ($rotate != 0)
	{
		$cmd = "jpegtran -copy all -outfile " . $tmp_file_rotated . " -rotate " . $rotate . " " . $img_file;
		`$cmd > /dev/null 2>&1`;
	}
}

if(getFileType($image) == "video")
{
	// extract the first frame in the video to use as thumbnail/small image
	$tmppath = "/tmp/" . randomNum(10) . "/";
	mkdir($tmppath);
	$tmpfile = $tmppath . "00000005.jpg";

	// mplayer generates a frame
	$cwd = getcwd();
	$mplayer_cmd = "cd $tmppath; $mplayer_path $mplayer_params '$cwd/$img_file' > /dev/null";
	
	system($mplayer_cmd);
	
	
	$src = imlib_load_image($tmpfile);
	
	// clean up when finished
	system("rm -rf $tmppath");
}
else
{
	$src = imlib_load_image($img_file);
} 

// get original image dimensions
$width = imlib_image_get_width($src);
$height = imlib_image_get_height($src);

if(($rotate == 90 || $rotate == 270) && ($type == "thumbnail" || $type == "small"))
{
	$tmp = $w;
	$w = $h;
	$h = $tmp;
}

// scale to preferred size $w, $h
//$dst = imlib_create_cropped_scaled_image($src, 0,0, $width,$height,$w, $h);

if ($type == "original")
{
	if ($rotate != 0)
	{
		$cmd = "jpegtran -copy all -rotate " .   $rotate . " " . escapeshellarg($img_file);
		Header('Content-type: image/jpeg');
		print `$cmd`;
		#$fp = popen($cmd,"r");
		#fpassthru($fp);
		#fclose($fp);
	}
	else
	{
		readfile($img_file);
	}

}
else
{
	$dst = imlib_create_image($w,$h);
	imlib_blend_image_onto_image($dst,$src,0,0,0, $width,$height,0,0,$w,$h,false,false,true);

	// leave a water mark if the image is a video frame
	// this makes it easy to tell which thumbnails are videos
	if(getFileType($image) == "video")
	{
		$watermark = imlib_load_image($movie_icon);
		if($watermark)
		{
			// resize the icon
			$wm_w = ceil($w/3);
			$wm_h = ceil($h/3);
			
			// make icon semi transparent
			imlib_image_modify_alpha($watermark, 150);
					
			$watermark = imlib_create_scaled_image($watermark, $wm_w, $wm_h);

			// draw a simple border to distinguish it from dark backgrounds
			$colour = 150;
			imlib_image_draw_rectangle($watermark, 0, 0, $wm_w, $wm_h, $colour, $colour, $colour, 255);

			// blend icon onto video frame
			imlib_blend_image_onto_image($dst,		// destination image
						     $watermark,	// source image
						     0,			// malpha
						     0,			// source x
						     0,			// source y
						     $wm_w,		// source width
						     $wm_h,		// source height
						     $w-$wm_w,		// dest x
						     $h-$wm_h,		// dest y
						     $wm_w,		// dest width
						     $wm_h,		// dest height
						     0,			// dither
						     1,			// blend
						     1);		// alias
			imlib_free_image($watermark);
		}
	}

/*	if(!$rotate)
	{
		$dst = imlib_create_cropped_scaled_image($src, 0,0, $width,$height,$w, $h);
	}
*/
	if($rotate)
	{
		if($rotate == 90 || $rotate == 270)
		{
			// width becomes height
			$tmp = $h;
			$h = $w;
			$w = $tmp;

			$tmp = $height;
			$height = $width;
			$width = $tmp;
		}

		if($type == "thumbnail")
		{
			$newimage = imlib_load_image($tmp_file_rotated);
			$n_w = imlib_image_get_width($newimage);
			$n_h = imlib_image_get_height($newimage);

			$newimage = imlib_create_cropped_image($newimage, ($n_w-$width)/2, ($n_h-$height)/2, $width, $height);
		}
		else
		{
			$newimage = imlib_create_rotated_image($src, $rotate);
			$n_w = imlib_image_get_width($newimage);
			$n_h = imlib_image_get_height($newimage);

			$newimage = imlib_create_cropped_image($newimage, ($n_w-$width)/2+2, ($n_h-$height)/2+1, $width, $height);
		}
		
		$dst = imlib_create_cropped_scaled_image($newimage, 0,0, $width,$height,$w, $h);
	}

	// dump as JPEG to the browser
	imlib_image_set_format($dst,"jpeg");			    
	Header('Content-type: image/' . imlib_image_format($src));
	if ($type=="thumbnail")
	{
		imlib_dump_image($dst,$err,$thumbnail_quality);
	}
	else if ($type == "small")
	{
		imlib_dump_image($dst,$err,$small_quality);
	}
	else
	{
		// default to 80
		imlib_dump_image($dst,$err,80);
	}

	// store if required (only thumbnails right now)
	if ($cmd == "store" && $type == "thumbnail")
	{
		if (false == file_exists($thumbdir)) 
			makeDirectory ($thumbdir);
		imlib_save_image($dst,$thumb_file, $err, $thumbnail_quality);
		chmod ($thumb_file,0664);
	}
	if ($cmd == "store" && $type == "small")
	{
		if (false == file_exists($smalldir)) 
			makeDirectory ($smalldir);
		imlib_save_image($dst,$small_file, $err, $small_quality);
		chmod ($small_file,0664);
	}

	imlib_free_image($src);
	imlib_free_image($dst);
	if (file_exists($tmp_file))
	{
		unlink($tmp_file);
	}
	if (file_exists($tmp_file_rotated))
	{
		unlink($tmp_file_rotated);
	}

}

?>

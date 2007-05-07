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
	$img_file = $tmpfile;
}

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
	if($rotate)
	{
		if($rotate == 90 || $rotate == 270)
		{
			// width becomes height
			$tmp = $h;
			$h = $w;
			$w = $tmp;
		}
		else
		{
		}
	}


	if ($type == "thumbnail")
	{
		$thumb=@exif_thumbnail($img_file,$wid,$hei,$tp);
		if ($thumb)
		{
			$pixbuf = pixbuf_new_from_data($thumb);
			$pixbuf = pixbuf_scale_simple($pixbuf,$w,$h);
		}

	}

	if (!$pixbuf)
	{	
		$pixbuf = pixbuf_new_from_file_at_size($img_file,$w,$h);
	}
	
	if ($rotate)
	{
		if (90 == $rotate)
			$gdk_rotation = GDK_PIXBUF_ROTATE_CLOCKWISE;
		else if (180 == $rotate)
			$gdk_rotation = GDK_PIXBUF_ROTATE_UPSIDEDOWN;
		else if (270 == $rotate)
			$gdk_rotation = GDK_PIXBUF_ROTATE_COUNTERCLOCKWISE;
		else
			$gdk_rotation = GDK_PIXBUF_ROTATE_NONE;

		$pixbuf = pixbuf_rotate_simple($pixbuf,$gdk_rotation);
	}

	if(getFileType($image) == "video" && $pixbuf)
	{
		$watermark = pixbuf_new_from_file($movie_icon);
		if($watermark)
		{
			// resize the icon
			$wm_w = pixbuf_get_width($watermark);
			$wm_h = pixbuf_get_height($watermark);
			
			// blend icon onto video frame
			pixbuf_composite(
				$watermark,
				$pixbuf,
				$w-ceil($w/3),
				$h-ceil($h/3),
				ceil($w/3),
				ceil($h/3),
				$w-ceil($w/3),
				$h-ceil($h/3),
				ceil($w/3) / $wm_w,
				ceil($h/3) / $wm_h,
				GDK_INTERP_BILINEAR,
				128);
		}
		else
		{
			echo "could not load watermark\n";
		}
	}

	// dump as JPEG to the browser
	Header('Content-type: image/jpeg');
	if ($pixbuf)	
	{
		// store if required (only thumbnails right now)
		if ($cmd == "store" && $type == "thumbnail")
		{
			if (false == file_exists($thumbdir)) 
				makeDirectory ($thumbdir);
			//imlib_save_image($dst,$thumb_file, $err, $thumbnail_quality);
			$success = pixbuf_dump_and_save($pixbuf,$thumb_file);
			if (!$success)
			{
				echo "write failed\n";
			}

			chmod ($thumb_file,0664);
		}
		else if ($cmd == "store" && $type == "small")
		{
			if (false == file_exists($smalldir)) 
				makeDirectory ($smalldir);
			//imlib_save_image($dst,$small_file, $err, $small_quality);
			pixbuf_dump_and_save($pixbuf,$thumb_file);
			chmod ($small_file,0664);
		}
		else
		{
			pixbuf_dump($pixbuf);
		}
	}
}


if(getFileType($image) == "video")
{
	// clean up when finished
	system("rm -rf $tmppath");
}

<?php
//==============================================================================
// global variables - set in config.ini
//==============================================================================

$config = parse_ini_file("config.ini",TRUE);

if (!isset($config['setup']['complete']))
{
	// show the setup screen
	errorRedirect("setup.php");
}

$data_dir = "data";
$separator = "/";
$picture_dir = $data_dir . $separator . "pictures";

if (!file_exists($picture_dir))
{
	if ( !symlink($config['directory']['pictures'],$picture_dir) )
	{
		errorRedirect("setup.php");
	}
}

$thumbnail_dir = $data_dir . $separator . "thumbnail";
$thumbnail_size = $config['image_thumbnail']['width'];
$thumbnail_quality = $config['image_thumbnail']['quality'];

$small_dir = $data_dir . $separator . "small";
$small_size = $config['image_small']['width'];
$small_quality = $config['image_small']['quality'];

$movie_icon = $config['movie']['overlay'];
$mplayer_path = $config['movie']['mplayer_path'];
$mplayer_params = $config['movie']['mplayer_params'];

$comment_dir = $data_dir . $separator . "comments";
$commentlog = $data_dir . $separator . "comment_log.txt";

$options_sortby = $config['options']['sortby'];

?>

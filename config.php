<?php
//==============================================================================
// global variables - set in config.ini
//==============================================================================

$config = parse_ini_file("config.ini",TRUE);

$base_directory = $config['picture']['dir'];

$pics_parent_dir = "/home/candice/public_html/pics/";

$thumbnail_dir = $config['image_thumbnail']['dir'];
$thumbnail_size = $config['image_thumbnail']['width'];
$thumbnail_quality = $config['image_thumbnail']['quality'];

$small_dir = $config['image_small']['dir'];
$small_size = $config['image_small']['width'];
$small_quality = $config['image_small']['quality'];

$movie_icon = $config['movie']['dir'];
$mplayer_path = $config['movie']['mplayer_path'];
$mplayer_params = $config['movie']['mplayer_params'];

$commentlog = $config['comment']['log'];

?>

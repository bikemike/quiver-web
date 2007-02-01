<?php
require_once("functions.php");

$dir = $_GET['dir'];
$image = $_GET['image'];
$delay = $_GET['delay'];
$type = $_GET['type'];
$view = $_GET['view'];
$gift = $_GET['gift'];

if($type == "") $type = "small";

$dir = getValidDirectory($dir);
$images = getImageList($dir);
$prev = getPrevImage($image,$images);
$next = getNextImage($image,$images);
$arr = split ("/",$_SERVER['SCRIPT_FILENAME']);

$dir = str_replace("\\","",$dir);
$image = str_replace("\\","",$image);

if($image == NULL)
	$image = $images[0];
if($image == NULL)
	errorRedirect("browse.php?dir=$dir&view=$view&type=$type");

if($delay > 0)
	print "<meta http-equiv=\"Refresh\" content=\"$delay;url=random.php?type=$type&view=$view&delay=$delay\">";

?>
<html>
<head><title></title>
<link REL="stylesheet" type="text/css" href="stylesheet.css" />
</head>
<body>
<center>
<?

showNavBar($image, $images, $dir, $type, $view);

if ($type == "small")
{
	print "<table cellspacing=1 cellpadding=5 width=100%>\n";
	print "<tr><th>index</th><th>$dir</th></tr>\n";
	print "<tr><td width=120>\n";
	showSimpleIndex($dir,$image,$images,$view);
	print "</td><td valign=top>\n";
	print "<center>\n";
	print getImageHTML("$dir/$image","small", $view, $rotate);

	$thumb_loc = "/data/gift_thumbnails/$dir/$image";
	$thumb_loc = str_replace(".jpg", "_thumbnail_jpg.jpg", $thumb_loc);
	$thumb_loc = str_replace(".JPG", "_thumbnail_JPG.jpg", $thumb_loc);
	$abs_thumb_loc = chop(`pwd`) . $thumb_loc;

	if(`pidof gift` != "" && getFileType($image) == "image" && file_exists($abs_thumb_loc)) // <-- only show gift button for images in the existing collection, while gift is running
	{
		$img_loc = "/$picture_dir/$dir/$image";
		$img_loc = str_replace(" ", "%20", $img_loc);
		$thumb_loc = str_replace(" ", "%20", $thumb_loc);
?>
		<form action="/gift/#searchForm" method="post">
		<input type="hidden" name="img_loc_0" value="<?php echo $img_loc; ?>">
		<input type="hidden" name="thumb_loc_0" value="<?php echo $thumb_loc; ?>">
		<input type="hidden" name="img_sim_0" value="1.000">
		<input type="hidden" name="img_rel_0" value="1">
		<input type="hidden" name="collectionId" value="c-34-51-22-16-8-106-6-258-0">
		<input type="hidden" name="algorithmId" value="a-cidf">
		<input type="hidden" name="result-size" value="24">
		<input type="submit" name="action" value="Find similar images">
		</form>
<?
	}
	print "</center>";
	print "<br><br>";

	$lines = @file(addPaths($comment_dir,addPaths($dir,$image)) . ".cmt");
	if($lines){
		print "<b>comments:</b><br><br>";
		foreach($lines as $line_num => $line){
		    print $line . "<br>";
		}
		print "<br>";
	}
	?>
	<b>post a comment:</b><br>
	<form id=commentform action=comment.php method=post>
	<?
	    print "<input type=hidden name=image value=\"$image\">";
	    print "<input type=hidden name=dir value=\"$dir\">";
	    print "<input type=hidden name=type value=\"$type\">";
	    print "<input type=hidden name=view value=\"$view\">";
            print "<input type=hidden name=dir value=\"$dir\">";
            for ($i=0; $i<8; $i++)
               $antispam .= chr(mt_rand(65, 90));
	?>

        name: <input type=text name=<? echo $antispam;?>name size=10> comment: 
	<input type=text name=<? echo $antispam;?>comment size=30>
	<input type=submit name=sumbit value=post>
        <input type="hidden" name="antispam" value="" id=antispam>
<script type="text/javascript">
    document.getElementById('commentform').style.display = 'block';
    document.getElementById('antispam').value = '<?echo $antispam; ?>';
</script>
<noscript>Sorry, you need JavaScript to post comments.</noscript>

	</form>
	</td></tr>
	</table>
	<?
}
elseif ($type == "original")
{
	print "<table cellspacing=1 cellpadding=5 width=100%>\n";
	print "<tr><th>$dir</th></tr>\n";
	print "<tr><td valign=top>\n";
	print "<center>" . getImageHTML("$dir/$image","original",$view, $rotate) . "</center>";
	print "<br><br>";

	$lines = @file(addPaths($comment_dir,addPaths($dir,$image)) . ".cmt");
	if($lines){
	print "<b>comments:</b><br><br>";
	foreach($lines as $line_num => $line){
	    print $line . "<br>";
	}
	print "<br>";
	}
	?>
	<b>post a comment:</b><br>
	<form action=comment.php method=post>
	<?
	print "<input type=hidden name=image value=\"$image\">";
	print "<input type=hidden name=dir value=\"$dir\">";
	print "<input type=hidden name=type value=\"$type\">";
	print "<input type=hidden name=view value=\"$view\">";
	?>

	name: <input type=text name=name size=10>
	comment: <input type=text name=comment size=30>
	<input type=submit name=sumbit value=post>
	</form>
	</td></tr>
	</table>
	<?
}
elseif ($type == "4x4")
{
	$next1 = getNextImage($image,$images);
	$next2 = getNextImage($next1,$images);
	$next3 = getNextImage($next2,$images);
	print "<table cellspacing=1 cellpadding=5 width=100%>\n";
	print "<tr><th colspan=2>$dir</th></tr>\n";
	print "<tr><td width=50% align=right valign=bottom>\n";
	print getImageHTML("$dir/$image","4x4", $view, $rotate);
	print "</td><td align=left valign=bottom>\n";
	print getImageHTML("$dir/$next1","4x4", $view, $rotate);
	print "</td></tr>\n";
	print "<tr><td align=right valign=top>\n";
	print getImageHTML("$dir/$next2","4x4", $view, $rotate);
	print "</td><td align=left valign=top>\n";
	print getImageHTML("$dir/$next3","4x4", $view, $rotate);
	print "</td></tr></table>\n";
}
elseif ($type == "allpage")
{
	for($i = 0; $i<count($images); $i++)
	{
		$image = $images[$i];
		print getImageHTML("$dir/$image", "small", $view, $rotate) . "<br>\n"; // problem with $rotate

		print "<br><br>";

		$lines = @file(addPaths($comment_dir,addPaths($dir,$image)) . ".cmt");
		if($lines){
			print "<b>comments:</b><br><br>";
			foreach($lines as $line_num => $line){
			    print $line . "<br>";
			}
			print "<br>";
		}
		?>
		<b>post a comment:</b><br>
		<form action=comment.php method=post>
		<?
		    print "<input type=hidden name=image value=\"$image\">";
		    print "<input type=hidden name=dir value=\"$dir\">";
		    print "<input type=hidden name=type value=\"$type\">";
		    print "<input type=hidden name=view value=\"$view\">";
		?>

		name: <input type=text name=name size=10>
		comment: <input type=text name=comment size=30>
		<input type=submit name=sumbit value=post>
		</form>
		<?
	}
	?>
	</td></tr>
	</table>
	<?
}

showNavBar($image, $images, $dir, $type, $view);
showCopyright();
?>

</body>
</html>

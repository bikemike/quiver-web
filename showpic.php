<?php
require_once("functions.php");

$dir = $_GET['dir'];
$image = $_GET['image'];
$delay = $_GET['delay'];
$type = $_GET['type'];
$view = $_GET['view'];

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

?></center><?
if ($dir != "") echo "Album: " . $dir;
?><center><?

if ($type != "original")
{
	print "<table cellspacing=1 cellpadding=5 width=100%>\n";
	print "<tr><th>index</th><th>$dir</th></tr>\n";
	print "<tr><td width=120>\n";
	showSimpleIndex($dir,$image,$images,$view);
	print "</td><td valign=top>\n";
	print "<center>\n";
}
else
{
	print "<table cellspacing=1 cellpadding=5 width=100%>\n";
	print "<tr><th>$dir</th></tr>\n";
	print "<tr><td valign=top>\n";
}

if ($type!="original")
{
	print getImageHTML("$dir/$image","small", $view, $rotate);
	/*
	print "<br>rotate: ";
	print "<a href=\"$PHP_SELF?image=$image&dir=$dir&view=$view&type=$type\">normal</a> | ";
	print "<a href=\"$PHP_SELF?image=$image&dir=$dir&view=$view&type=$type&rotate=90\">90</a> | ";
	print "<a href=\"$PHP_SELF?image=$image&dir=$dir&view=$view&type=$type&rotate=180\">180</a> | ";
	print "<a href=\"$PHP_SELF?image=$image&dir=$dir&view=$view&type=$type&rotate=270\">270</a>";
	*/
}

else print "<center>" . getImageHTML("$dir/$image","original",$view, $rotate) . "</center>";
print "<br><br>";

if($type != "original")
print "</center>";

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
showNavBar($image, $images, $dir, $type, $view);
showCopyright();
?>

</body>
</html>

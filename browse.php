<?php
require_once("functions.php");
// fix directory information
$dir = $_GET['dir'];
$dir = getValidDirectory($dir);
$view = $_GET['view'];
$imagelist = getImageList($dir);
$dirs = getDirectoryList($dir);

if($_REQUEST['format'] == "rss")
{
	Header("Content-type: application/rss+xml");
	showDirectoryIndexRss($dir,$dirs,$view);
	return;
}

?>
<html>
<head><title></title>
<link REL="stylesheet" type="text/css" href="stylesheet.css" />
</head>
<body>
<center>
<?
showNavBar(NULL, NULL, $dir, $type, $view);
// print the directory listing
print "<table cellspacing=1 cellpadding=5 width=100%><tr><td>";
if ($dir != "") echo "<br>Album: " . $dir;
print "</td></tr><tr><td>";

if(count($dirs) > 0)
	showDirectoryIndex($dir,$dirs,$view);

print "<br><center>";

if ($view=="detailed") showDetailedIndex($dir,$imagelist,$view);
else showCompactIndex($dir,$imagelist,$view);
print "</tr></td></table>";
showNavBar(NULL, NULL, $dir, $type, $view);
showCopyright();
?>

</body>
</html>

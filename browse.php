<?php
require_once("functions.php");
?>
<html>
<head><title></title>
<link REL="stylesheet" type="text/css" href="stylesheet.css" />
<center>
<?

// fix directory information
$dir = $_GET['dir'];
$view = $_GET['view'];
?><pre><?
$dir = fixDirectory($dir);
$imagelist = getImageList($dir);
$dirs = getDirectoryList($dir);

showNavBar(NULL, NULL, $dir, $type, $view);

// print the directory listing
print "<table cellspacing=1 cellpadding=5 width=100%><tr><td>";
if ($dir != ".") echo $dir;
print "</td></tr><tr><td>";

if(count($dirs) > 0)
	showDirectoryIndex($dir,$dirs,$view);

print "<br><center>";

if ($view=="detailed") showDetailedIndex($dir,$imagelist,$view);
else showCompactIndex($dir,$imagelist,$view);
print "</tr></td></table>";

showNavBar(NULL, NULL, $dir, $type, $view);
?>
php script by <a href="http://morrison.dyndns.org/">mike morrison</a> (c) 2002, modified by donn morrison

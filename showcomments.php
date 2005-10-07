<?php
require_once("functions.php");
$view = $_GET['view'];
$limit = $_GET['limit'];
?>
<html>
<head> <title> comments </title>
</head>
<link REL="stylesheet" type="text/css" href="stylesheet.css" />
<body>
<?php
$search_dir = $comment_dir . DIRECTORY_SEPARATOR;

$lines; // store sorted lines

if (strstr($_SERVER['SERVER_SOFTWARE'],"Linux"))
{
	$handle = popen("find $search_dir -iname \"*.cmt\"","r");

	while ($line=trim(fgets($handle)))
	{
		$lines[$line]=filemtime($line);
	}
	pclose ($handle);
}
else
{
	$search_dir = str_replace("/","\\",$search_dir);
	$handle = popen("dir $search_dir*.cmt /s /b","r");
	while ($line=trim(fgets($handle)))
	{
		$line = strstr($line,$search_dir);
		$line = str_replace("\\","/",$line);
		$lines[$line]=filemtime($line);
	}
	pclose($handle);
}

$num_lines = sizeof($lines);
if (0 < $num_lines )
{
	asort($lines,SORT_NUMERIC);
	$lines = array_reverse($lines,TRUE);

	echo "<table><tr><th>thumbnail</th><th>info</th><th>comments";
	if (0 >= $limit || !$limit || $limit >= $num_lines)
	{
		$limit = $num_lines;
	}
	else
	{
		echo " (<a href='$PHP_SELF?view=$view'>show all</a>)";
	}
	echo "</th></tr>";
	$count = 0;
	foreach ($lines as $line => $tstamp)
	{
		$image = substr($line,strlen($search_dir),strlen($line) -strlen($search_dir) -4);
		echo "<tr><td>";
		echo getImageHTML($image,"thumbnail",$view);
		echo "</td><td>";

    		$path_parts = pathinfo($image);
    		$dir = $path_parts["dirname"];

		$exif = @exif_read_data ($image,'IFD0');
		if ($exif)
		{
			$datetime = $exif['DateTimeOriginal'];
			if ($datetime)
			{
				echo str_replace(" ","<br>",$exif['DateTimeOriginal']) . "<br><br>";
			}
		}
		$index = "<a href=\"browse.php?dir=$dir&view=$view\">see album</a>";
		echo $index; 
		echo "</td><td>";
		$hcmt = fopen ($line,"r");
		while ($cl = trim (fgets($hcmt)))
		{
			echo $cl . "<br>";
			/*
			$comment = new Comment;
			$comment->parseComment($cl);
			echo "<table width=100%><tr><th>";
			echo "Posted by " . $comment->name . " on <i>" . $comment->date . "</i>";
			echo "</th></tr><tr><td>" . $comment->comment;
			echo "</td></tr></table><br>";
			*/
		}
		fclose ($hcmt);
		echo "</td></tr>";
		$count++;
		if ($count >= $limit)
		{
			break;
		}
	}
	echo "</table>";

}
else
{
	echo "no comments";
}
?>

</body>
</html>

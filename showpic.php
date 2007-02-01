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
	print "</center>";
	print "<br><br>";

	showComments($image, $images, $dir, $type, $view);
}
elseif ($type == "original")
{
	print "<table cellspacing=1 cellpadding=5 width=100%>\n";
	print "<tr><th>$dir</th></tr>\n";
	print "<tr><td valign=top>\n";
	print "<center>" . getImageHTML("$dir/$image","original",$view, $rotate) . "</center>";
	print "<br><br>";

	showComments($image, $images, $dir, $type, $view);
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

		showComments($image, $images, $dir, $type, $view);

	}
	?>
	</td></tr>
	</table>
	<?
}

showNavBar($image, $images, $dir, $type, $view);
showCopyright();

function showComments($image, $images, $dir, $type, $view)
{
	?><div class=comments><?
	$comment_dir = $GLOBALS['comment_dir'];
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
	</div>
        <?

}

?>

</body>
</html>

<?php
require_once("functions.php");

if (isset($_POST['antispam'])) {
    $antispam = $_POST['antispam'];
    $name = $_POST[$antispam . 'name'];
    $comment = $_POST[$antispam . 'comment'];
}

$dir = stripslashes($_POST['dir']);
$image = $_POST['image'];
$type = $_POST['type'];
$view = $_POST['view'];
    if($comment)
    {
		$realDir = addPaths($comment_dir,$dir);
		makeDirectory($realDir);
		$commentFile = addPaths($realDir,$image) . ".cmt";
        $fp = fopen($commentFile, "a");
        if($fp)
        {
            $date = date("Y.m.d.H:i", time());
            if($name == "") $name = "anonymous";
            $comment = deHTML($comment);
            $name = deHTML($name);
            fwrite($fp, "<font color=gray>".$name." @ ".$date.":</font> ".$comment."\n");
            fclose($fp);

            $fp2 = fopen($commentlog, "a");
            if($fp2)
            {
                fwrite($fp2, $dir."/".$image."\n".$_SERVER['REMOTE_ADDR'].": ".$_SERVER['HTTP_USER_AGENT']."\n".$name." @ ".$date.": ".$comment."\n\n");
                fclose($fp2);
            }
        }
        else
        {
//            print "could not open comment file<br>";
        }

        errorRedirect("showpic.php?image=$image&dir=$dir&type=$type&view=$view");
        die;
    }
?>

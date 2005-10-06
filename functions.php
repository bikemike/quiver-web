<?php
require_once("config.php");

function getFileType($filename)
{
    $ext = strrchr($filename, ".");
    $ext = strtolower($ext);
    switch($ext)
    {
    case '.jpg':
    case '.jpeg':
    case '.gif':
    case '.bmp':
    case '.png':
        return "image";
    case '.avi':
    case '.mpg':
    case '.mpeg':
    case '.mov':
        return "video";
    default:
        return "unknown";
    }
}

function randomNum($numDigits)
{
    if ($numDigits <= 0)
      return(0);
       
    for ($i=1; $i <= $numDigits; ++$i) {
        $val .= rand(0,9);
    }       
return($val);
}

function deHTML($string)
{
    $string = str_replace("\\\"", "\"", $string);
    $string = str_replace("\\'", "'", $string);
    $string = str_replace("&", "&amp;", $string);
    $string = str_replace("<", "&lt;", $string);
    $string = str_replace(">", "&gt;", $string);
    return $string;
}

function getRandomImage($dir, $type, $view, $delay)
{
	$thumbnail_dir = $GLOBALS['thumbnail_dir'];
	$small_dir = $GLOBALS['small_dir'];
	$base_dir = $GLOBALS['base_directory'];
	$tmpfile = randomNum(10);

	$lines=array(); // store sorted lines
	if (strstr($_SERVER['SERVER_SOFTWARE'],"Linux"))
	{
		$handle = popen("find $base_dir/ -path '$thumbnail_dir' -prune -o -path '$small_dir' -prune -o -name '*.*' -print","r");
		while ($line=trim(fgets($handle)))
		{
			array_push($lines,$line);
		}
		pclose ($handle);
	}
	else
	{
		$handle = popen("dir $base_dir\\*.jpg /s /b","r");
		while ($line=trim(fgets($handle)))
		{
			$line = strstr($line,$base_dir);
			$line = str_replace("\\","/",$line);
			if (!strstr($line,$thumbnail_dir) && !strstr($line,$small_dir))
			{
				array_push($lines,$line);
			}
		}
		pclose ($handle);
	}
	
	$line_number = rand(0, sizeof($lines));
	
	$path_parts = pathinfo($lines[$line_number]);
	$path_parts["basename"] = chop($path_parts["basename"]);
	$path_parts["basename"] = urlencode($path_parts["basename"]);

	if($delay)
		$delay = "&delay=$delay";
	else
		$delay = "";
	$url = "showpic.php?dir=" . $path_parts["dirname"] . "&image=" . $path_parts["basename"] . "&type=$type&view=$view$delay";


	chdir_base();
	
	errorRedirect($url);
}

//==============================================================================
// html functions
//==============================================================================

function showNavBar($image, $images, $dir, $type, $view)
{
	$prev_dir = getNextDirectory($dir);
	$next_dir = getPrevDirectory($dir);
	if($image && $images)
	{
		$prev = getPrevImage($image,$images);
		$next = getNextImage($image,$images);
		if($prev)
			$prev = "<a href=\"$PHP_SELF?image=$prev&dir=$dir&type=$type&view=$view\">&lt; prev image</a>";
		else
			$prev = "&lt; prev image";
		if($next)
			$next = "<a href=\"$PHP_SELF?image=$next&dir=$dir&type=$type&view=$view\">next image &gt;</a>";
		else
			$next = "next image &gt;";
		$index = "<a href=\"browse.php?dir=$dir&view=$view\">index</a>";
	}
	else
	{
		$prev = "&lt; prev image";
		$next = "next image &gt;";
		$index = "index";
	}
	
	if($prev_dir)
	{
		$images = getImageList($prev_dir);
		$prev_dir = "<a href=\"$PHP_SELF?dir=$dir/../$prev_dir&view=$view\">&lt; prev album</a>";
	}
	else
		$prev_dir = "&lt; prev album";
	if($next_dir)
	{
		$images = getImageList($prev_dir);
		$next_dir = "<a href=\"$PHP_SELF?dir=$dir/../$next_dir&view=$view\">next album &gt;</a>";
	}
	else
		$next_dir = "next album &gt;";
	
	$up_dir = "<a href=\"$PHP_SELF?dir=$dir/..&view=$view\">up a level</a>";

	print "<table width=100%><tr><td width=33% align=center>";
	print "$prev_dir | $up_dir | $next_dir";
	print "</td><td width=33% align=center>";
	print "$prev | $index | $next";
	print "</td><td width=33% align=center>";
	if ($view == "detailed")
	{
	    print "<a href=\"$PHP_SELF?dir=$dir&image=$image&type=$type&view=compact\">compact</a>";
	}
	else
	{
	    print "<a href=\"$PHP_SELF?dir=$dir&image=$image&type=$type&view=detailed\">detailed</a>";
	 }
	 print " | <a href=\"showcomments.php?view=$view&limit=15\">comments</a>";
	 print " | <a href=\"random.php?type=$type&view=$view\">random image</a>";
	print "</td></tr></table>";
}

function hexview($data){
 $bytePosition = $columnCount = $lineCount = 0;
 $columns = 20;
 $dataLength = strlen($data);
 $return = array();
 $return[] = '<table border="0" cellspacing="0" cellpadding="2">';
 for($n = 0; $n < $dataLength; $n++){
   $lines[$lineCount][$columnCount++] = substr($data, $n, 1);
   if($columnCount == $columns){
     $lineCount++;
    $columnCount = 0;
   }
 }
 foreach($lines as $line){
  $return[] = '<tr><td align="right">'.$bytePosition.': </td>';
   for($n = 0; $n < $columns; $n++){
     $return[] = '<td>'.strtoupper(bin2hex($line[$n])).'</td>';
   }
  $return[] = '<td> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </td>';
   for($n = 0; $n < $columns; $n++){
    $return[] = '<td>'.(htmlentities($line[$n]) ? htmlentities($line[$n]) : '&nbsp;').'</td>';
   }
  $return[] = '</tr>';
   $bytePosition = $bytePosition + $columns;
 }
 $return[] = '</table>';
 return implode('', $return);
}

function getImageHTML ($filePath,$type, $view, $rotate="") {
    $file = $filePath;
    $path_parts = pathinfo($filePath);

    chdir ($path_parts["dirname"]);
    
	if(getFileType($filePath) == "image")
	{
		$exif = exif_read_data ($path_parts["basename"],'IFD0');
		$orientation = $exif['Orientation'];
		if ($orientation == 6){
			$rotate += 90;
		} elseif ($orientation == 1){
			$rotate += 0;
		} elseif ($orientation == 3){
			$rotate += 180;
		} elseif ($orientation == 8){
			$rotate += 270;
		}
		else
			$rotate += 0;
			
/*		if($type == "small")
		{
			print "<table>";
			foreach($exif as $key=>$section) {
				foreach($section as $name=>$val) {
					print "<tr>";
					if ($name == "MakerNote"){
						//$val = bin2hex($val);
						echo "<td>$key.$name: </td><td>" . hexview ($val) . "</td>\n";
					}else{
						echo "<td>$key.$name: </td><td>$val</td>\n";
					}
					print "</tr>";
				}
			}
			print "</table>";
		}*/
	}

    if (is_dir($path_parts["basename"])){
        $class = " class=folder";
        $arr = split ("/",$path_parts["dirname"]);
        $len = sizeof($arr);
        $info = $arr[$len-1];
        if ($path_parts["basename"] == "..") $img = "images/folder_up.gif";
        else $img = "images/folder.gif";
        $imagehtml = "<img$class src=\"$img\" border=0 alt=\"$info\" width=20 height=15>";
    }else{
        $info = $path_parts["basename"] . " (". ceil((filesize($path_parts["basename"]))/1024) . " KB)";
        if ($type == "original"){
	    if(getFileType($filePath) == "image")
	    {
                $linkStart = "<a href=\"$PHP_SELF?image=" . $path_parts["basename"]."&dir=".$path_parts["dirname"]."&type=small&view=$view&rotate=$rotate\">";
                $imagehtml = getOriginalImage($path_parts["dirname"],$path_parts["basename"],$rotate);
	    }
	    else if(getFileType($filePath) == "video")
	    {
                $imagehtml = getSmallImage($path_parts["dirname"],$path_parts["basename"],$rotate);
                $linkStart = "<a href=\"$filePath\">";
	    }
	    else
	    {
	        $imagehtml = "(view)";
                $linkStart = "<b>" . $path_parts["basename"] ."</b> has unknown file type <a href=\"$filePath\">";
	    }
        }
        if ($type == "small"){
	    if(getFileType($filePath) == "image")
	    {
                $imagehtml = getSmallImage($path_parts["dirname"],$path_parts["basename"],$rotate);
                $linkStart = "<a href=\"$PHP_SELF?image=" . $path_parts["basename"]."&dir=".$path_parts["dirname"]."&type=original&view=$view&rotate=$rotate\">";
	    }
	    else if(getFileType($filePath) == "video")
	    {
                $imagehtml = getSmallImage($path_parts["dirname"],$path_parts["basename"]);
                $linkStart = "<a href=\"$filePath\">";
	    }
	    else
	    {
	        $imagehtml = "(view)";
                $linkStart = "<b>" . $path_parts["basename"] ."</b> has unknown file type <a href=\"$filePath\">";
	    }

        }
        if ($type == "thumbnail"){
            if(getFileType($filePath) == "image" || getFileType($filePath) == "video")
            {
                $linkStart = "<a href=\"showpic.php?image=" . $path_parts["basename"]."&dir=".$path_parts["dirname"]."&view=$view\">";
                $imagehtml = getThumbnailImage($path_parts["dirname"],$path_parts["basename"], $rotate);
            }
            else
            {
                $linkStart = "<a href=\"showpic.php?image=" . $path_parts["basename"]."&dir=".$path_parts["dirname"]."&view=$view\">";
                $imagehtml = "<b>" . $path_parts["basename"] ."</b>";
            }
        }
        $linkEnd = "</a>";
    }
    $imageHTML .= "$linkStart$imagehtml$linkEnd";
    chdir_base();
    return $imageHTML;
}

function createURL () {
    $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);
    $url =  $path_parts['basename'];
    $url .= "?";
    foreach ($_GET as $key => $value) {
        $url .="&$key=$value";
    } 
    return $url;
}


function showSimpleIndex ($dir,$current,$list, $view){
    $display = 5;
    $index=0;
    $middle;
    //find the image
    for ($i=0;$i < sizeof($list);$i++){
        if ($current == $list[$i])  $index = $i;
    }
    if ($display%2 ==1) $middle = intval($display/2);
    else $middle = $display / 2 - 1;
   


    $start = $index - $middle;
    if ($start < 0) $start = 0;
    $end = $start + $display;
    
    if (sizeof($list) < $end){
        $start += sizeof($list) - $end;
        if ($start < 0) $start = 0;
        $end = sizeof($list);
    }

    print "<table width=110>";
    if ($start > 0) print "<td>&nbsp; . . . </td>";
    else  print "<td width=110>&nbsp;</td>";
    for ($i=$start;$i < $end;$i++){
        if ($current == $list[$i]) $id = " class=selected";
        else $id= "";
        print "<tr$id><td class=thumbnail>";
        print getImageHTML($dir . "/" . $list[$i],"thumbnail", $view);
        print "</td></tr>\n";
    }
    if ($end < sizeof($list)) print "<td>&nbsp; . . . </td>";
    else  print "<td>&nbsp;</td>";
    print "</table>";
}

function showCompactIndex ($dir,$list, $view){
        $columns = 5;
        $counter = 0;
        ?>
        <table border=0 cellpadding=3 cellspacing=1 width=100%>
        <tr>
        <?
        for ($i=0;$i< sizeof($list) ; $i++){
            print "<td class=thumbnail>";
            print getImageHTML($dir . "/" . $list[$i],"thumbnail", $view);
            print "<!--<br><input type=checkbox name='images[]' value='$list[$i]'>--></td>";
            $counter++;            
			if ($counter %$columns == 0){
				print "</tr><tr>";
			}
        }
        if ($counter > $columns){
            while ($counter%$columns != 0){
			    print "<td></td>";
			    $counter++;
		    }
        }
        print "</tr></table>"; 

}

function showDirectoryIndex ($dir,$dirs, $view){
    ?>
    <table border=0 cellpadding=3 cellspacing=1 width=100%>
    <tr bgcolor="#cccccc"><th width=10>directory</th><th width=20>pics</th><th width=150>album name</th></tr>
    <?
    $color = "odd";
    for ($i=0;$i< sizeof($dirs) ; $i++){

        print "<tr class=\"$color\"><td><a href=\"$PHP_SELF?dir=$dir/$dirs[$i]&view=$view\">";
        print getImageHTML($dir. "/" . $dirs[$i],"folder", $view);
        $num_pics = count(getImageList($dir."/".$dirs[$i],1));
        print "</a></td><td>$num_pics</td><td>";
        if($dirs[$i] != "..")
            print "<a href=\"$PHP_SELF?dir=$dir/$dirs[$i]&view=$view\">" . $dirs[$i] . "</a></td></tr>";
	else
	    print "<a href=\"$PHP_SELF?dir=$dir/$dirs[$i]&view=$view\">up a level</a></td><!--<td>&nbsp;</td>--></tr>";
        if ($color == "odd"){
            $color = "even";
        }else{
            $color = "odd";
        }
    }
    print "</table>"; 
}

function showDetailedIndex ($dir,$list,$view){
	$thumbnail_dir = $GLOBALS['thumbnail_dir'];
        $dir = fixDirectory($dir);
        
        ?>
        <table border=0 cellpadding=3 cellspacing=1 width=100%>
        <tr bgcolor="#cccccc"><th width=100>thumbnail</th><!--<th width=20>save</th>--><th width=120>info</th><th width=400>comments</th></tr>
        <?
	$color="odd";
        for ($i=0;$i< sizeof($list) ; $i++){
            print "<tr class=\"$color\"><td class=thumbnail width=110 align=center>";
            print getImageHTML($dir . "/" . $list[$i],"thumbnail", $view);
            print "</td><!--<td class=checkbox><input type=checkbox name='images[]' value='$list[$i]'>--></td><td>";
            chdir($dir);
	    
	    if(getFileType($list[$i]) == "image")
	    {
	        $size = getimagesize($list[$i]);
		$dimensions = "<br>". $size[0]."x".$size[1];
	    }
	    else
	    {
	        $dimensions = "";
            }
            print "<b>".getFileType($list[$i])."</b><br>" .$list[$i] . $dimensions . "<br>" . ceil(filesize($list[$i])/1024) . " KB</td>";
            print "<td>";
            $lines = @file($thumbnail_dir . "/" . $dir . "/" . $list[$i] . ".cmt");
            if($lines)
                foreach($lines as $line_num => $line){
                    print $line . "<br>";
                }
            print "</td></tr>";
            chdir_base();
            if ($color == "odd"){
                $color = "even";
            }else{
                $color = "odd";
            }
        }
        print "</table>";        

}


function getSmallImage ($dir,$file,$rotate=""){

    $small_dir = $GLOBALS['small_dir'];
    $small_size = $GLOBALS['small_size'];
    if(getFileType($file) == "image")
        $size = getimagesize ($file) or $size = Array($small_size,$small_size*3/4);
    else
        $size = Array(320,240); //assume size for video FIXME




    $ratio = $size[1] / $size[0];

    if($rotate == 90 || $rotate == 270)
    {
        $tmp = $width;
	$width = $height;
	$height = $tmp;
	$ratio = $size[0] / $size[1];
    }
    
    if($size[1] > $small_size || $size[0] > $small_size)
    {
	if ($ratio < 1){
		$width = $small_size;
		$height = $width*$ratio;
	}else{
		$height = $small_size;
		$width = $height/$ratio;
	}
    }
    else
    {
        $width = $size[0];
	$height = $size[1];
    }
    

    
    $path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);
    $imgsrcw = $width;
	$imgsrch = $height;
    	if($rotate == 90 || $rotate == 270)
	{
        	$imgsrcw = $height;
		$imgsrch = $width;
	}
    if (file_exists($path_parts['dirname']."/$small_dir/$dir/$file")) return "<img class=small src=\"$small_dir/$dir/$file\" width=$width height=$height\">";
    else
        return "<img class=small src=\"getpic.php?cmd=store&type=small&w=$width&h=$height&image=$file&dir=$dir&rotate=$rotate\" width=$width height=$height\">";
    //    return "<img class=small src=\"getpic.php?cmd=store&type=small&w=$width&h=$height&image=$file&dir=$dir&rotate=$rotate\" width=$imgsrcw height=$imgsrch\">";
}

function getThumbnailImage ($dir,$file,$rotate=""){
    $thumbnail_size = $GLOBALS['thumbnail_size'];
    $thumbnail_dir = $GLOBALS['thumbnail_dir'];
    if(getFileType($file) == "image")
	$size = getimagesize ($file) or $size = Array($thumbnail_size,$thumbnail_size*3/4);
    else
        $size = Array($thumbnail_size,$thumbnail_size*3/4);
	
    $ratio = $size[1] / $size[0];
    
    if($rotate == 90 || $rotate == 270)
    {
        $tmp = $width;
	$width = $height;
	$height = $tmp;
	$ratio = $size[0] / $size[1];
    }
    

    if ($ratio < 1){
        $width = $thumbnail_size;
        $height = $width*$ratio;
    }else{
        $height = $thumbnail_size;
        $width = $height/$ratio;
    }    


    $path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);

    if (file_exists($path_parts['dirname']."/$thumbnail_dir/$dir/$file")) return "<img class=thumbnail src=\"$thumbnail_dir/$dir/$file\" width=$width height=$height\">";
    else
        return "<img class=thumbnail src=\"getpic.php?cmd=store&type=thumbnail&w=$width&h=$height&image=$file&dir=$dir&rotate=$rotate\" width=$width height=$height\">";
}

function getOriginalImage ($dir,$file,$rotate=""){
    if(getFileType($file) == "image")
	$size = getimagesize ($file) or $size = Array(640,480);
    else
        $size = Array(320,240); //assume size for video FIXME

    $width = $size[0];
    $height = $size[1];

    if($rotate == 90 || $rotate == 270)
    {
	    $width = $size[1];
	    $height = $size[0];
    }
 

    $path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);
    if ($rotate)
        return "<img class=original src=\"getpic.php?type=original&w=$width&h=$height&image=$file&dir=$dir&rotate=$rotate\" width=$width height=$height\">";
    return "<img class=original src=\"$dir/$file\" width=$width height=$height\">";
}

function errorRedirect ($location,$error=NULL){
        if ($error){
                if (!strpos($location,'?')) $location .= '?' . "error=" . urlencode($error);
                else $location .=  "&error=". urlencode($error);
        }
        header("Location: $location");
        exit;
}


//==============================================================================
// file and directory functions
//==============================================================================

function makeDirectory ($directory){
    $dirs = split ("/", $directory);
    $i = 0;
    $current = "";
    while ($i != sizeof ($dirs)){
        $current = $current . $dirs[$i] . "/";
        print $current . "<br>";
        if (false == file_exists($current)){
            mkdir($current,0775);
            chmod ($current,0775); 
        }        
        $i++;
    }
        
}

function fixDirectory ($dir){
    $dir = stripslashes($dir);
    if (!$dir || $dir=="") $dir = $GLOBALS["base_directory"];
    else{
        $dirs = split ("/", $dir) ;
        $i=0;
    
        while ($i < sizeof ($dirs))
	{
            if (!$dirs[$i]) { 
                array_splice ($dirs, $i, 1);
                $i--;
            }else if ($dirs[$i] == ".."){
                if ($i == 0){
                    array_splice ($dirs, $i, 1);

                }else{
                    if ($dirs[$i-1] != "."){
                        array_splice ($dirs, $i-1, 2);
                        $i--;
                    }else{
                        array_splice ($dirs, $i, 1);
                    }
                }
                $i--;            
            }else if ( $dirs[$i] == "."){
                if ($i != 0){
                    array_splice ($dirs, $i, 1);
                    $i--;
                }
            }
            $i++;
        }

        $dir = join("/",$dirs);
    }
    if (!file_exists($dir) || $dir == ".") {
        $dir = $GLOBALS["base_directory"];
    }
    return $dir;
}
//gets a directory listing relative to the current directory
function getDirectoryList ($dir){
    fixDirectory($dir);
    chdir($dir);
    $dirs = array();
    if ($handle = @opendir(".")){
        readdir($handle);
        readdir($handle);
        while (false !== ($file = readdir($handle))) { 
            if (@is_dir($file)){
                if($file != ".thumbnails")
                array_push($dirs,$file);
            }
        }
     }
     chdir_base();
     sort($dirs);
     $dirs = array_reverse($dirs);
     return $dirs;
}
function chdir_base () {
    $arr = split ("/",$_SERVER['SCRIPT_FILENAME']);
    array_pop ($arr);
    chdir(join("/",$arr));
}

function getPrevDirectory ($dir) {
    $dir = fixDirectory($dir);
    chdir($dir);
    
    if ($dir == $GLOBALS["base_directory"]){
        chdir_base();
        return "";
    }
    $dirs = explode (DIRECTORY_SEPARATOR, getcwd()) ;
    $old = array_pop($dirs);
    chdir ("..");
    
    $list = getDirectoryList(".");

    foreach($list as $num => $entry)
    {
        if($entry == "..") continue;
        if($entry == $old){
            chdir_base();
            return $last;
        }
        $last = $entry;
    }
    chdir_base();
    return "";
}

function getNextDirectory ($dir){
    $dir = fixDirectory($dir);
    chdir($dir);
    if ($dir == $GLOBALS["base_directory"]){
        chdir_base();
        return "";
    }
    $dirs = explode (DIRECTORY_SEPARATOR, getcwd()) ;

    $old = array_pop($dirs);
    chdir ("..");
    $list = getDirectoryList(".");

    foreach($list as $num => $entry)
    {
        if($entry == "..") continue;
        if ($last == $old){
            chdir_base();
            return $entry;
        }
        $last = $entry;
    }
    chdir_base();
    return "";
}

function getPrevImage ($image,$list) {
   for ($i = 0;$i < sizeof($list);$i++){
        if ($list[$i] == $image){
            return $last;
        }
        $last = $list[$i];
    }
    return "";
}

function getNextImage ($image,$list){
   for ($i = 0;$i < sizeof($list);$i++){
        if ($last == $image){
            return $list[$i];
        }
        $last = $list[$i];
    }
    return "";
}

function getImageList ($dir){
    $dir = fixDirectory($dir);
    chdir($dir);
    $images = array(); //array to hold image list
    
    if ($handle = @opendir(".")) {
        readdir($handle);
        readdir($handle);

        while (false !== ($file = readdir($handle))) {
            if (false == @is_dir($file)){
//                if(getFileType($file) != ""){
                    array_push($images,$file);
//                }
            }
        }
    }
    chdir_base();
    sort($images);
    return $images;
}
class comment
{
	var $name;
	var $date;
	var $comment;

	function parseComment($raw_comment)
	{
		//$raw_comment = strip_tags($raw_comment);
		$arr = explode("@",$raw_comment);
		$n = strip_tags(array_shift($arr));
		$raw_comment = implode("@",$arr);

		$arr = explode(":</font>",$raw_comment);
		$d = strip_tags(array_shift($arr));
		$c = strip_tags(implode(":</font>",$arr));

		$this->name = trim($n);
		$this->date = trim($d);
		$this->comment = trim($c);
	}
}

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
	$picture_dir = $GLOBALS['picture_dir'];
	$small_dir = $GLOBALS['small_dir'];
	$base_dir = $GLOBALS['base_directory'];
	$tmpfile = randomNum(10);

	$lines=array(); // store sorted lines
	if (strstr($_SERVER['SERVER_SOFTWARE'],"Linux"))
	{
		$cmd = "find $picture_dir/ -name '*.*' -print";
		$handle = popen($cmd,"r");
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

	$file = $lines[$line_number];
	$file = str_replace($picture_dir . "/","",$file);
	
	$path_parts = pathinfo($file);
	$path_parts["basename"] = chop($path_parts["basename"]);
	$path_parts["basename"] = urlencode($path_parts["basename"]);

	if($delay)
		$delay = "&delay=$delay";
	else
		$delay = "";
	$url = "showpic.php?dir=" . $path_parts["dirname"] . "&image=" . $path_parts["basename"] . "&type=$type&view=$view$delay";

	
	errorRedirect($url);
}


//==============================================================================
// html functions
//==============================================================================

function showNavBar($image, $images, $dir, $type, $view)
{
	// switch around to show last first
	$prev_dir = getNextDirectory($dir);
	$next_dir = getPrevDirectory($dir);

	$dir = substr($dir,strlen($GLOBALS['base_directory']));
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
		$prev_dir = "<a href=\"$PHP_SELF?dir=$prev_dir&view=$view\">&lt; prev album</a>";
	}
	else
		$prev_dir = "&lt; prev album";
	if($next_dir)
	{
		$images = getImageList($next_dir);
		$next_dir = "<a href=\"$PHP_SELF?dir=$next_dir&view=$view\">next album &gt;</a>";
	}
	else
		$next_dir = "next album &gt;";
	
	$dirs = explode (DIRECTORY_SEPARATOR, $dir) ;
	array_pop($dirs);
	$parent = join(DIRECTORY_SEPARATOR,$dirs);

	echo $parent;
	if ("" == $dir)
		$up_dir = "up a level";
	else
		$up_dir = "<a href=\"$PHP_SELF?dir=$parent&view=$view\">up a level</a>";

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
	$parts = pathinfo($filePath);
	$parent = $parts["dirname"];

	$file = $filePath;
	$realPath = addPaths($GLOBALS['picture_dir'] , $file);
	$path_parts = pathinfo($file);

	
	if(getFileType($filePath) == "image")
	{
		$exif = exif_read_data ($realPath,'IFD0');
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
	}

	if (is_dir($realPath)){
		$class = " class=folder";
		$arr = split ("/",$realPath);
		$len = sizeof($arr);
		$info = $arr[$len-1];
		$img = "images/folder.gif";
		$imagehtml = "<img$class src=\"$img\" border=0 alt=\"$info\" width=20 height=15>";
	}else{
		$info = $realPath . " (". ceil((filesize($realPath))/1024) . " KB)";
		if ($type == "original"){
		if(getFileType($filePath) == "image")
		{
				$linkStart = "<a href=\"$PHP_SELF?image=" . $path_parts["basename"]."&dir=".$parent."&type=small&view=$view&rotate=$rotate\">";
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
				$linkStart = "<a href=\"$PHP_SELF?image=" . $path_parts["basename"]."&dir=".$parent."&type=original&view=$view&rotate=$rotate\">";
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
				$linkStart = "<a href=\"showpic.php?image=" . $path_parts["basename"]."&dir=".$parent."&view=$view\">";
				$imagehtml = getThumbnailImage($path_parts["dirname"],$path_parts["basename"], $rotate);
			}
			else
			{
				$linkStart = "<a href=\"showpic.php?image=" . $path_parts["basename"]."&dir=".$parent."&view=$view\">";
				$imagehtml = "<b>" . $path_parts["basename"] ."</b>";
			}
		}
		$linkEnd = "</a>";
	}
	$imageHTML .= "$linkStart$imagehtml$linkEnd";
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
			print getImageHTML(addPaths($dir, $list[$i]),"thumbnail", $view);
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
	$full_dir = $dir;
	$dir = substr($dir,strlen($GLOBALS['base_directory']));
	
	?>
	<table border=0 cellpadding=3 cellspacing=1 width=100%>
	<tr bgcolor="#cccccc"><th width=10>directory</th><th width=20>pics</th><th width=150>album name</th></tr>
	<?
	$color = "odd";
	for ($i=0;$i< sizeof($dirs) ; $i++){

		print "<tr class=\"$color\"><td><a href=\"$PHP_SELF?dir=$dir/$dirs[$i]&view=$view\">";
		print getImageHTML($full_dir. "/" . $dirs[$i],"folder", $view);
		$num_pics = count(getImageList($full_dir."/".$dirs[$i],1));
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
	?>
	<table border=0 cellpadding=3 cellspacing=1 width=100%>
	<tr bgcolor="#cccccc"><th width=100>thumbnail</th><!--<th width=20>save</th>--><th width=120>info</th><th width=400>comments</th></tr>
	<?
	$color="odd";
	foreach ($list as $item)
	{
		$realPath = addPaths ($thumbnail_dir, addPaths($dir,$item));
		print "<tr class=\"$color\"><td class=thumbnail width=110 align=center>";
		print getImageHTML($dir . "/" . $item,"thumbnail", $view);
		print "</td><!--<td class=checkbox><input type=checkbox name='images[]' value='$item'>--></td><td>";
	
		if(getFileType($item) == "image")
		{
			$size = getimagesize(addPaths(addPaths($GLOBALS['picture_dir'],$dir), $item));
			$dimensions = "<br>". $size[0]."x".$size[1];
		}
		else
		{
			$dimensions = "";
		}
		print "<b>".getFileType($item)."</b><br>" .$item . $dimensions . "<br>" . ceil(filesize($realPath)/1024) . " KB</td>";
		print "<td>";
		$lines = @file(addPaths($comment_dir,addPaths($dir , $item)) .".cmt");
		if($lines)
		{
			foreach($lines as $line_num => $line)
			{
				print $line . "<br>";
			}
		}
		print "</td></tr>";
		if ($color == "odd")
		{
			$color = "even";
		}
		else
		{
			$color = "odd";
		}
	}
	print "</table>";		

}


function getSmallImage ($dir,$file,$rotate="")
{
	$picture_dir = $GLOBALS['picture_dir'];
	$small_dir = $GLOBALS['small_dir'];
	$small_size = $GLOBALS['small_size'];

	
	$smallPath = addPaths($small_dir,addPaths($dir,$file));
	$realPath = addPaths($picture_dir,addPaths($dir,$file));

	if(getFileType($file) == "image")
		$size = getimagesize ($realPath) or $size = Array($small_size,$small_size*3/4);
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
	if (file_exists($smallPath)) 
		return "<img class=small src=\"$smallPath\" width=$width height=$height\">";
	else
		return "<img class=small src=\"getpic.php?cmd=store&type=small&w=$width&h=$height&image=$file&dir=$dir&rotate=$rotate\" width=$width height=$height\">";
}

function getThumbnailImage ($dir,$file,$rotate=""){
	$thumbnail_size = $GLOBALS['thumbnail_size'];
	$thumbnail_dir = $GLOBALS['thumbnail_dir'];

	$realPath = addPaths($GLOBALS['picture_dir'] , addPaths($dir,$file) );

	if(getFileType($file) == "image")
		$size = getimagesize ($realPath) or $size = Array($thumbnail_size,$thumbnail_size*3/4);
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
	
	if ($ratio < 1)
	{
		$width = $thumbnail_size;
		$height = $width*$ratio;
	}
	else
	{
		$height = $thumbnail_size;
		$width = $height/$ratio;
	}	


	$path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);

	if (file_exists(addPaths($thumbnail_dir,addPaths($dir,$file)))) 
		return "<img class=thumbnail src=\"" . addPaths($thumbnail_dir,addPaths($dir,$file)) . "\" width=$width height=$height\">";
	else
		return "<img class=thumbnail src=\"getpic.php?cmd=store&type=thumbnail&w=$width&h=$height&image=$file&dir=$dir&rotate=$rotate\" width=$width height=$height\">";
}

function getOriginalImage ($dir,$file,$rotate=""){
	$picture_dir = $GLOBALS['picture_dir'];
	$realPath = addPaths($picture_dir,addPaths($dir,$file));
	
	if(getFileType($file) == "image")
	$size = getimagesize ($realPath) or $size = Array(640,480);
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
	return "<img class=original src=\"$realPath\" width=$width height=$height\">";
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
		if (false == file_exists($current)){
			mkdir($current,0775);
			chmod ($current,0775); 
		}		
		$i++;
	}
		
}

//gets a directory listing relative to the current directory
function getDirectoryList ($dir=""){
	$dir = addPaths($GLOBALS['picture_dir'] , $dir);
	$dirs = array();
	if ($handle = opendir($dir))
	{
		readdir($handle);
		readdir($handle);
		while (false !== ($file = readdir($handle)))
		{
			if (is_dir(addPaths($dir,$file)))
			{
				array_push($dirs,$file);
			}
		}
	}
	sort($dirs);
	$dirs = array_reverse($dirs);
	return $dirs;
}
function chdir_base ($subdir="") {
	$arr = split (DIRECTORY_SEPARATOR,$_SERVER['SCRIPT_FILENAME']);
	array_pop ($arr);
	chdir (join(DIRECTORY_SEPARATOR,$arr));
}

function getPrevDirectory ($dir) {
	if ("" == $dir)
		return "";
	$dirs = explode (DIRECTORY_SEPARATOR, $dir) ;
	$old = array_pop($dirs);

	$parent = join(DIRECTORY_SEPARATOR,$dirs);
	$list = getDirectoryList($parent);

	$last = "";
	foreach($list as $num => $entry)
	{
		if($entry == $old && $last != "")
		{
			return addPaths($parent,$last);
		}
		$last = $entry;
	}
	return "";
}

function getNextDirectory ($dir){
	if ("" == $dir)
		return "";
	$dirs = explode (DIRECTORY_SEPARATOR, $dir) ;
	$old = array_pop($dirs);

	$parent = join(DIRECTORY_SEPARATOR,$dirs);
	$list = getDirectoryList($parent);

	$last = "";
	foreach($list as $num => $entry)
	{
		if ($entry == "..")
			continue;
		if ($last == $old)
		{
			return addPaths($parent,$entry);
		}
		$last = $entry;
	}
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

// system paths
function addPathsSys($path1,$path2)
{
	return addPaths($path1,$path2,DIRECTORY_SEPARATOR);
}
// web paths
function addPathsWeb($path1,$path2)
{
	return addPaths($path1,$path2,"/");
}
function addPaths($path1,$path2,$separator="/")
{
	$pos1 = strrpos($path1,$separator);
	$pos2 = strpos($path2,$separator);
	$idx1 = strlen($path1) -1;
	$idx2 = 0;
	
	if (0 == strlen($path2))
	{
		return $path1;
	}
	if (0 == strlen($path1))
	{
		return $path2;
	}

	if ($pos1 !== $idx1 && $pos2 !== $idx2)
	{
		$new_path = $path1 . $separator . $path2;
		return $new_path;
	}
	else if ($pos1 === $idx1 && $pos2 === $idx2)
	{
		$new_path = $path1 . substr($path2,1);
		return $new_path;
	}
	else
	{
		$new_path = $path1 . $path2;
		return $new_path;
	}
}

function getImageList ($dir){
	$dir = addPaths($GLOBALS['picture_dir'] , $dir);
	$images = array(); //array to hold image list
	
	if ($handle = @opendir($dir)) {
		readdir($handle);
		readdir($handle);

		while (false !== ($file = readdir($handle))) {
			if (false == @is_dir($file)){
				array_push($images,$file);
			}
		}
	}
	sort($images);
	return $images;
}

// ensure the directory is valid
function getValidDirectory($dir)
{
	$pic_dir = $GLOBALS['picture_dir'];
	$pic_link_dir = realpath($pic_dir);

	$dir = addPaths($pic_link_dir, $dir);
	$dir = realpath($dir);
	
	$dir = str_replace($pic_link_dir,$pic_dir,$dir);

	$pos = strpos($dir,$pic_dir);
	
	if (FALSE !== $pos)
	{
		$dir = substr($dir,$pos + strlen($pic_dir));	
	}
	else
	{
		$dir = "";
	}
	if (0 == strpos($dir,DIRECTORY_SEPARATOR))
		$dir = substr($dir,1);
	return $dir;
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

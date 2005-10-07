<?

$path_parts = pathinfo($_SERVER['SCRIPT_FILENAME']);
$directory =  $path_parts['dirname'];

?>
<html>
<head><title></title>
<link REL="stylesheet" type="text/css" href="stylesheet.css" />
</head>
<body>

<div id=content>
<h1>Setting up Quiver-Web</h1>
Follow these steps to get quiver-web set up on your machine:
<ol>
<li>Create a directory called <code>data</code> in the script directory:<blockquote><code>$ cd <?=$directory?><br>$ mkdir data</code></blockquote></li>
<li>Set the permissions on the directory:
<blockquote><code>$ chmod 777 data</code></blockquote>
<b>NOTE:</b> this may not be very secure so you might want to try setting the ownership to www-data and restricting the permissions as follows:
<blockquote><code>$ su<br># chown www-data:www-data data<br># chmod 700 data<br># exit</code></blockquote>
</li>
<li>edit the config.ini and change the line that says:
<blockquote><code>;complete=1</code></blockquote>
to
<blockquote><code>complete=1</code></blockquote>
and specify the location of your pictures:
<blockquote><code>[directory]<br>pictures=/mnt/storage/pictures</code></blockquote>

</li>
</ol>

<div style="clear:both"></div>

</div>

</body>
</html>

<?php
$filename = 'bar.txt';

$file = fopen($filename, 'r+');
//rewind($file);
//$content = fread($file, filesize($filename));
//fseek($file, filesize($filename));
fwrite($file,  'test');
fflush($file);
echo ftell($file) . PHP_EOL;
fwrite($file, '123');
echo ftell($file);
//fflush($file);
//echo fread($file, filesize($filename));
fclose($file);
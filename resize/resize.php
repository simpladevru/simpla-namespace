<?php

use Root\api\Simpla;

require_once __DIR__ . '/../bootstrap/app.php';

$filename = $_GET['file'];
$token = $_GET['token'];

if(!Simpla::$container->config->check_token($filename, $token)) {
	exit('bad token');
}

$resized_filename = Simpla::$container->image->resize($filename);

if(is_readable($resized_filename))
{
	header('Content-type: image');
	print file_get_contents($resized_filename);
}


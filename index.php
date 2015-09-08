<?php
require_once('vendor/autoload.php');
require_once('seostyle.css');

$uri = null;
$ifacetype = null;
$form = require_once('seoform.html');

if(isset($argv[1]) && !empty($argv[1])){
	$uri = $argv[1];
	$ifacetype = 'cli';
}
else{
	if(isset($_POST['url']) && !empty($_POST['url']) ){
		$uri = $_POST['url'];
		$ifacetype = 'web';
	}
	else{
		die($form);
	}
}

$uri .= '/feed/custom/3304/?include_variants=true&limit=500&page=';

$zip = new ZipArchive;
$tmpZipfileName = tempnam(sys_get_temp_dir(), 'SEOZIP') . '.zip';
$res = $zip->open($tmpZipfileName, ZipArchive::CREATE);

if($res !== true) {
	die('Unable to create zip');
}

$page = 1;
do{
	$response = Httpful\Request::get($uri . $page)->followRedirects()->send();
	$returnCode = (int)$response->code;
	if($returnCode == 200){
		$zip->addFromString($page . '.xml', $response->raw_body);
		$page++;
	}
}while($returnCode == 200);

$zip->close();

if($ifacetype == 'cli'){
	rename($tmpZipfileName, realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . time() . '.zip');
}else{
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($tmpZipfileName).'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($tmpZipfileName));
	readfile($tmpZipfileName);

	unlink($tmpZipfileName);
}

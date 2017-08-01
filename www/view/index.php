<?php
$str_url = "";
$pos = strpos($_SERVER['REQUEST_URI'],"/?");
if ($pos === false) $pos = 1000;
if ($pos > 0) {
	$pos--;
	$str_url = substr($_SERVER['REQUEST_URI'],1,$pos);
}
$url = array();
$url = explode("/",$str_url);

$index_dir = TEMPLATE_DIR."/";
$index_file = $index_dir."index.".FILE_TYPE;
$main = "";
foreach ($url as $dir) {
	if (is_dir($index_dir.$dir)) {
		$index_dir .= $dir."/";
		if (file_exists($index_dir."index.".FILE_TYPE)) {
			$index_file = $index_dir."index.".FILE_TYPE; 
		} else {
			$main = $dir;
			break;
		}
	} else {
		$main = $dir;
		break;
	}
}

$html = file_get_contents($index_file);

$html = require_file($index_dir,$html);
if ($main == "") $main = "home"; 
$main_file = $index_dir.$main.".".FILE_TYPE;

if (file_exists($main_file)) {
	$replace = file_get_contents($main_file);
	$find = "[main]";
	$html = str_replace($find,$replace,$html);
}


$index_replace = array("TEMPLATE_URL"=>TEMPLATE_URL,
					   "SITE_URL"=>SITE_URL,
					   "SITE_URL_ADMIN"=>SITE_URL_ADMIN); 
					   
foreach ($index_replace as $key=>$value) {
	$find = "[".$key."]";
	$html = str_replace($find,$value,$html);
}

$html = $lg->ReplaceView($html);

echo $html;
exit;

function require_file ($index_dir,$html) {
	
	$find = "[file:";
	$offset = 0;
	$pos = strpos($html,$find,$offset);
	while ($pos !== false) {
		$pos2 = strpos($html,"]",$pos);
		$len = $pos2 - $pos - 6;
		$file_name = substr($html,$pos+6,$len);
		$file_url = $index_dir.$file_name.".".FILE_TYPE;
		if (file_exists($file_url)) {
			$replace = file_get_contents($file_url);
			$f = "[file:".$file_name."]";
			$html = str_replace($f,$replace,$html);
		}
		
		if (strlen($html) < $pos2) return $html;
		$pos = strpos($html,$find,$pos2);
	}
	return $html;
}


function d($arr,$r=false) {
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
	if ($r===false) exit;
}
?>
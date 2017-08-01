<?php
require_once(__ROOT__."/classes/Lang.php");
$lg = new Language();
$lg->AddReplaceFile("main");

require_once (__ROOT__."/classes/url.php");
$url = new URL;
$aurl = $url->ArrayURL();

if (count($aurl) > 0 && $aurl[0] != "") {
	$lg->AddReplaceFile($aurl[0]);
	
	$route_controller = __ROOT__."/controllers";
	$route_index = array();
	$route_temp = __ROOT__."/controllers";
	$last_file = "/index";
	foreach ($aurl as $value) {	
		$route_temp .= "/".$value;
		if (is_dir($route_temp)) {
			array_push($route_index,$route_temp."/index.php");
			$route_controller .= "/".$value;
		} else {
			$last_file = "/".$value;		
			break;
		}
	}
	if (isset($_GET['action'])) $f = "/".$_GET['action']; else $f = "index";
	$route_controller .= $last_file.".php";
	//echo $route_controller; exit;
	foreach($route_index as $file) {
		if (file_exists($file)) require_once($file);
	}
	if (file_exists($route_controller)) require_once($route_controller); else  { header('HTTP/1.0 404 not found'); exit; }
	
	if ($view) {
		require_once(__ROOT__."/classes/Page.php");
		$page = new Page;
		if ($page->GetPage($value)) {
			$lg->replace['page_title'] = $page->title;
			$lg->replace['page_meta_d'] = $page->meta_d;
			$lg->replace['page_meta_k'] = $page->meta_k;
			$lg->replace['page_text'] = $page->text;
		}
	}
} else {
	$home_controller = __ROOT__."/controllers/".$aurl[0]."/home.php";
	$aurl[1] = "home";
	if (file_exists($home_controller)) require_once($home_controller);
}
?>
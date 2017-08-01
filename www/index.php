<?php
session_start();

include (__DIR__."/config.php");
if (isset($_COOKIE['template']) && is_dir("templates/".$_COOKIE['template']."/")) define("TEMPLATE",$_COOKIE['template']);
define("TEMPLATE_URL",SITE_URL."/view/templates/".TEMPLATE);

if (isset($_GET['partner'])) SetCookie('partner',$_GET['partner'],time()+31536000,"/",DOMAIN);

$view = false;
include (__ROOT__."/controllers/index.php");
if ($view == false) exit;



if (!is_dir(__ROOT__."/view/templates/".TEMPLATE."/")) 
	define("TEMPLATE_DIR","templates/".TEMPLATE."/");
else
	define("TEMPLATE_DIR",__ROOT__."/view/templates/".TEMPLATE."/");

require_once (__ROOT__."/view/index.php");
?>
<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'alfa');

define('SMTP_USER', 'info@vlevels.ru');
define('SMTP_PASSWORD', 'gtUyerywb8422'); 
define('SMTP_NAME', 'Alfa Discount Group');
define('SMTP_HOST', 'ssl://smtp.yandex.ru');

define('__ROOT__',$_SERVER['DOCUMENT_ROOT']);
define('SITE_NAME', 'Alfa Discount Group');
define('DOMAIN', 'alfa.quik-site.com');
define('SITE_URL', 'http://alfa.quik-site.com');
define('SITE_URL_ADMIN', 'http://alfa.quik-site.com/admin'); 
define('API_SITE_URL', 'http://api.alfa.quik-site.com');

define('TEMPLATE', 'default');
define('FILE_TYPE','html');
define('DEFAULT_LANG', 'ru');

define('USER_HASH_KEY', '3ab7bf51ddf830be02604def50c5b3c8');
define('USER_AUTH_KEY', 'h1k3452bk23g5jbt23zvm8t84361k32d');

$tpl_insert = array("header",
					"left-bar",
					"right-bar",
					"footer");
?>
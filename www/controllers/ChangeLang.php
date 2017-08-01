<?php
if (isset($_GET['lang'])) setcookie("lang", $_GET['lang'], time()+86400*91, "/", ".".$_SERVER['SERVER_NAME']); echo $_SERVER['HTTP_REFERER'];
header("Location: ".$_SERVER['HTTP_REFERER']);
?>
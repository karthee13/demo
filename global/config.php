<?php error_reporting (E_ALL ^ E_NOTICE ^ E_DEPRECATED);
session_start();
ob_start();
require_once('query_factory.php');
require_once('common_functions.php');
$db = new queryFactory();
if($_SERVER['HTTP_HOST'] == 'localhost' ) {
	$db->connect('localhost','root','','demo');
} 
if ($db->error_number) die("Connection Error");
$title = 'demo';

?> 
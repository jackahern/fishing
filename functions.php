<?php
function dd($dump) {
	echo "<pre>";
	print_r($dump);
	exit;
}
function redirect($msg) {
	$_SESSION['msg'] = $msg;
	header("Location: fishing-with-html5.php");
	die();
}
<?php

$userid=$_GET['s'];
$auth=$_GET['p'];

$phash= hash('sha256', $auth);

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');

$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

$sql="select count(*) from users where userid = ? and password= ? ;";
$stmt = $pdo->prepare($sql);

$stmt->execute([$userid,$phash]);

$rows = $stmt->fetch() ;
$row = $rows["count"];

$ajxres=array();
$ajxres['count']=$row;
sendajax($ajxres);

$pdo=null;

function sendajax($ajx) {
	// encode the ajx array as json and return it.
	$encoded = json_encode($ajx);
	exit($encoded);
}

?>
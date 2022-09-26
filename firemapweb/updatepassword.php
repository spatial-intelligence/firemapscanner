<?php

$pw=$_POST['at1'];
$oldpw=$_POST['oldpw'];
$userid=$_POST['userid'];

$oldpwhash= hash('sha256', $oldpw);
$phash= hash('sha256', $pw);


$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');

$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

$sql= "UPDATE users set password = ? where userid=? and password =?;";
$stmt = $pdo->prepare($sql);
$stmt->execute([$phash,$userid,$oldpwhash]);	
$retval = $stmt ->rowCount();

$pdo = null;


if($retval==0 ){
  die("Sorry, could not update PASSWORD");
}
else
{
  header("location: projectlist_you.php");
}


?>
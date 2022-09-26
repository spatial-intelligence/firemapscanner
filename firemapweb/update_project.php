
<?php

if(isset($_REQUEST))
{

$active=$_POST['a'];
$projectid=$_POST['pid'];
$projectname=htmlentities($_POST['p'],ENT_QUOTES);
$emails=htmlentities($_POST['e'],ENT_QUOTES);

//var_export($projectid);

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');

$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

$sql= "update project set projectname=?, active=?, notification_emailaddress=? where projectid =? ;";
$stmt = $pdo->prepare($sql);

$stmt->execute([$projectname,$active,$emails,$projectid]);	
$pdo = null;


header("Location: projectlist_you.php");
die();

}
?>

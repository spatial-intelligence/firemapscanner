<?php
	session_start();
	if(!isset($_SESSION['usercode']))
	{
		header("location: login_user.php");
		die();
	}
?>


<?php

$p=$_GET['projectid'];
$usercode = $_SESSION['usercode'];

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');

$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

$sql= "DELETE from project where projectid= ?  and owner_userid = ?;";
$stmt = $pdo->prepare($sql);

$stmt->execute([$p,$usercode]);	

$resultcount = $stmt->rowCount();

//-------------------only delte and monitoring zones if this user was able to delete the project (ie the project owner)

if ($resultcount >0) {

		$sql2= "DELETE from monitorzones where projectid= ? ;";
		$stmt2 = $pdo->prepare($sql2);
		$stmt2->execute([$p]);	

		$sql3= "DELETE from userproject where projectid= ? ;";
		$stmt3 = $pdo->prepare($sql3);
		$stmt3->execute([$p]);	

}


//add to interaction  log
$sql= "INSERT into interaction_history (sid,datetime,action,value) values (-990,now(),?,?);";
$stmt = $pdo->prepare($sql);
$stmt->execute(['project deleted:'.$p,$usercode]);	

$pdo = null;


header("location: projectlist_you.php");

?>
<?php

if(isset($_REQUEST))
{

$projectid=$_POST['p'];
$projectname=htmlentities($_POST['t'],ENT_QUOTES);
$active=$_POST['c_active'];
$userid =$_POST['userid'];


$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
        
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

//update projects
$sql= "INSERT into project (projectid,projectname,startdate,active,owner_userid) values (?,?,'now()',?::boolean,?);";
$stmt = $pdo->prepare($sql);
$stmt->execute([$projectid,$projectname,$active,$userid]);	

if(! $stmt ){die("<br><h1>!!!!!!!!</h1><h1>Problem with adding this record!<br>Check ProjectID doesn't already exist.</h1>");}


//update userproject permissions
$sql2= "INSERT into userproject (userid,projectid) values (?,?);";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute([$userid,$projectid]);	


//add to interaction  log
$sql3= "INSERT into interaction_history (sid,datetime,action,value) values (-890,now(),?,?);";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute(['project added:'.$projectid,$userid]);	

$pdo = null;


header("Location: projectlist_you.php");
die();


}
?>

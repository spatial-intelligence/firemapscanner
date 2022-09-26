<?php
	session_start();
	if(!isset($_SESSION['usercode']))
	{
		header("location: login_user.php");
		die();
	}
?>

<?php
$prid=$_GET['projectid'];

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
		
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);
		
$sql="select case
when count(*) > 0 then (select '<div class=\"alert\">Project Exists Already</div>'
from project where projectid = ? )
when count(*) = 0 then '<div class=\"confirm\">OK</div>'
end as title
from project where projectid = ? ;";

$result = $pdo->prepare($sql);
$result->execute([$prid,$prid]);	
$row = $result->fetch() ;


$ajxres=array();
$ajxres['projectname']=$row["title"];
sendajax($ajxres);

$pdo = null;

function sendajax($ajx) {
	// encode the ajx array as json and return it.
	$encoded = json_encode($ajx);
	exit($encoded);
}
?>
<?php

	$dbset =  $_GET['dbset'];
	$polyid =  $_GET['polyid'];

try {
	$username = getenv('DBFIRE_USERNAME');
        $password = getenv('DBFIRE_PASSWORD');
	$dbhost = 'localhost';
	$dbname='nasafiremap';

	$connec = new PDO("pgsql:host=$dbhost;dbname=$dbname", $username, $password);
}
catch (PDOException $e) {
	echo "Error : " . $e->getMessage() . "<br/>";
	die();
}

if ($polyid =='all' )
{
   $sql = "select date_trunc('day',acqdate)::date as dt,count (*) as c from $dbset group by date_trunc('day',acqdate) order by date_trunc('day',acqdate)";
}
else{
	$sql = "select date_trunc('day',acqdate)::date as dt,count (*) as c from $dbset a join monitorzones b on st_intersects (a.geom,b.geom) where b.polyid=$polyid group by date_trunc('day',acqdate) order by date_trunc('day',acqdate)";
}
 
$statement=$connec->prepare($sql);
$statement->execute();
$results=$statement->fetchAll(PDO::FETCH_ASSOC);


 echo json_encode($results);
 
 flush();
    ob_flush();
    sleep(0.5);
    exit(0);
 
 $connec=null;

?>

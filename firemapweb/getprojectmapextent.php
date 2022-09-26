<?php
$pid=$_GET['projectid'];


$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
		
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);
		
$sql = "select st_xmin(ext) ,st_ymin(ext),st_xmax(ext), st_ymax(ext) from (select st_extent(st_transform(geom,4326)) as ext from monitorzones where projectid= ?) as e;";
$result = $pdo->prepare($sql);
$result->execute([$pid]);	


$ajxres=array(); // place to store the geojson result

	while ($row= $result->fetch() )
	{
	$xmin=$row[0];
	$ymin=$row[1];

	$xmax=$row[2];
	$ymax=$row[3];

	$f=array();
    $f['xmin']=$xmin;
    $f['ymin']=$ymin;
    $f['xmax']=$xmax;
    $f['ymax']=$ymax;
}

	$ajxres['box']=$f;
	sendajax($ajxres);

    pg_close($link);


function sendajax($ajx) {
	// encode the ajx array as json and return it.
	$encoded = json_encode($ajx);
	exit($encoded);
}


?>
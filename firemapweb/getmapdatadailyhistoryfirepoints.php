<?php

$pid=$_GET['projectid'];
$dbset=$_GET['dbset'];

$opt = [
    PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false 
];

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
		
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password, $opt);

$result = $pdo->query("SELECT st_x(geom) as longitude, st_y(geom) as latitude, confidence,acq_date,acq_time, source FROM dailyreporthistory  where projectid= '$pid' and acq_date=$dbset::date ");

# Build GeoJSON feature collection array
$geojson = array(
    'type'      => 'FeatureCollection',
    'features'  => array()
 );

foreach($result AS $row)
{
    $properties = $row;
    $feature = array(
         'type' => 'Feature',
         'properties' => $properties
    );
    # Add feature arrays to feature collection array
    array_push($geojson['features'], $feature);
}

header('Content-type: application/json');
echo json_encode($geojson, JSON_NUMERIC_CHECK);
$pdo = null;

?>


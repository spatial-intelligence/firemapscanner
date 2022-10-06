<?php

$dt1=$_GET['date1'];
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

$result = $pdo->query("SELECT a.latitude,a.longitude, a.confidence,a.acq_date,a.acq_time FROM $dbset a join monitorzones b on st_dwithin (a.geom,b.geom,0.001) where b.projectid= '$pid' and a.acqdate='$dt1'::date ");

# Build GeoJSON feature collection array
$geojson = array(
    'type'      => 'FeatureCollection',
    'features'  => array()
 );

foreach($result AS $row)
{
    $properties = $row;
    # Remove geojson and geometry fields from properties
    unset($properties['geojson']);
    unset($properties['the_geom']);
    $feature = array(
         'type' => 'Feature',
         'geometry' => json_decode($row['geojson'], true),
         'properties' => $properties
    );
    # Add feature arrays to feature collection array
    array_push($geojson['features'], $feature);
}

header('Content-type: application/json');
echo json_encode($geojson, JSON_NUMERIC_CHECK);
$pdo = null;
?>


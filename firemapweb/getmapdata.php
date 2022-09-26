<?php

$prid=$_GET['projectid'];

$opt = [
    PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false 
];


$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
		
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password, $opt);

$result = $pdo->query("SELECT polyid,projectid, ST_AsGeoJSON(ST_Transform(geom, 4326)) AS geojson FROM monitorzones where projectid=$prid");

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
$conn = NULL;
?>


<?php

$data=$_POST['d'];
$pid=$_POST['p'];

echo "<script type='text/javascript'>console.log($data)</script>";

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');

$link = pg_Connect("dbname=nasafiremap user=$username password=$password");

$sql= pg_exec($link, "DELETE FROM monitorzones where projectid=$pid;");

$sql= pg_exec($link, "
INSERT INTO monitorzones (projectid,geom)
SELECT
'$pid',
ST_SETSRID(ST_GeomFromGeoJSON(feat->>'geometry'),4326) AS geom FROM
(
SELECT json_array_elements(fc->'features') AS feat
FROM
 (
  SELECT '$data'::json AS fc
 ) AS f
) as ff;");


pg_close($link);

?>


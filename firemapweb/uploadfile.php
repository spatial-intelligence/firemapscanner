<!DOCTYPE html>
<html>

<head>

<title>Project Choices</title>
<link rel="stylesheet" href="style.css" />

<?php
include('header_alerts.php');


$userid=$_SESSION['usercode'];
$projectid=$_GET['projectid'];
?>

<style>
p {
  font-size: 0.875em; /* 14px/16=0.875em */
}
</style>

</head>


<body>
<div id="loginBox">
<form action="do_fileupload.php" method="post" enctype="multipart/form-data">
 <h2> Select Polygon Dataset to Upload to Project:   <?php echo ($projectid); ?>  </h2> 
 
 <p>
 Files accepted are: <br><br>
  &nbsp; &nbsp;&nbsp;   &nbsp;    .geojson (GeoJSON format) <br>
  &nbsp; &nbsp;&nbsp;   &nbsp;    .gpkg (GeoPackage format) <br>
  &nbsp; &nbsp;&nbsp;   &nbsp;    .zip (ESRI shapefile zipped into single archive)
<br><br>
These can continue multiple polygons, and use the EPSG:4326  (WGS84) coordinate system.</p>
  <br><br>
  <input type="hidden" name="projectid" id="projectid"  value = <?php echo($projectid); ?> >
  <input type="hidden" name="userid" id="userid"  value = <?php echo($userid); ?> >

  <br><br>

  <input type="file" name="fileToUpload" id="fileToUpload">  <br><br>  <br><br>
  <input type="submit" value="Upload Polygon Map Data" name="submit">  <br><br>
</form>

</div>


</body>
</html>





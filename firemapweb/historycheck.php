
<!DOCTYPE html>
<html>
<head>

<?php
include('header_alerts.php');

$userid=$_SESSION['usercode'];
$projectid=$_GET['projectid'];

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
	
            
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

    $sql="select project.projectid,project.active,project.projectname,project.notification_emailaddress from project join userproject on project.projectid = userproject.projectid where project.projectid=?  and userproject.userid= ?  ;";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([$projectid, $userid]);	
    $resultcount = $stmt -> rowCount() ;
    $row = $stmt->fetch();
    //var_export($row);
    $pdo = null;

    // Check if any records found based on userid and permissions
    if ($resultcount ==0) {
        echo ("You do not have permission to view this record");
        die;
    }

    //count how many records in this project - if too many then remove edit tools and simplify the view of the map
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

    $sql="select count(*) from monitorzones where projectid=? ;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$projectid]);	
    $count = $stmt->fetch();
    $polycount = $count["count"];
    $pdo = null;

?>



    <meta charset=utf-8 />
    <title>Map Tools</title>

    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
    <title>Leaflet.draw vector editing handlers</title>
    <script src="libs/leaflet-src.js"></script>
    <link rel="stylesheet" href="libs/leaflet.css"/>
    <link rel="stylesheet" href="libs/MarkerCluster.css" />
	  <link rel="stylesheet" href="libs/MarkerCluster.Default.css" />
	  <script src="libs/leaflet.markercluster-src.js"></script>

<style>
    body {
        margin: 0;
        padding: 0;
    }
    #map {
        position: absolute;
        bottom: 0;
        width:100%;
        top: 100px;
        z-index: 1;
    }

</style>

<script src="libs/jquery-1.11.1.min.js"></script>
<script src="libs/jquery-ui.js"></script>
<link rel="stylesheet" href="libs/jquery-ui.css">

<script>
  var datasetfilter_date = null;
  var datasetfilter_dbset = null;
</script>

</head>
<body>

<h1>Fire History for project: <?php echo($projectid) ?></h1>
<span id="startdate"    
  style="
     position: fixed;
    top: 60px;
    left: 500px;
    z-index: 499;"
  >

 Date: <input type='text' id='datepicker'>  Dataset:</span>
<select class='dbselector' name="dbset"  style="
     position: fixed;
    top: 60px;
    left: 780px;
    z-index: 499;"
   >
	
    
  <?php 
      $pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

      $sql="select tablename from datatablesindex order by tablename";
      $result = $pdo->prepare($sql);
      $result->execute();	
      $c = $result ->rowCount();

      while ($row = $result->fetch() ) 
      {
        echo "<option value=' " . $row['tablename'] . " '>".$row['tablename']."</option>";
      }

      $pdo = null;

  ?>
</select>

<span id='status' style="color:red">  &nbsp;&nbsp; Loading data...</span>

<div id='map'></div>

<script>
  var alertzones = new L.geoJson();
  var markers = L.markerClusterGroup();


  ESRImapLink = '<a href="http://www.esri.com/">Esri</a>';
  ESRIwholink ='i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
    
      var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
              osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
              osm = L.tileLayer(osmUrl, { maxZoom: 19, attribution: osmAttrib }),
              map = new L.Map('map', { center: new L.LatLng(0.1, -0.01), zoom: 2 }),
              drawnItems = L.featureGroup().addTo(map);


      L.control.layers({
          'osm': osm.addTo(map),
          "google": L.tileLayer('https://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}', {attribution: 'google',maxZoom: 19}),
          "ESRI":  L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {attribution: '&copy; '+ESRImapLink+', '+ESRIwholink,maxZoom: 18})
      }, { 'monitor zones': alertzones }, { position: 'topleft', collapsed: false }).addTo(map);
      
      map.addLayer(markers);


function showchartresult(polyid) {
         datasetfilter_dbset = datasetfilter_dbset.replace(/\s+/g, '');  // remove spaces
        var url = 'linegraph.php?dbset='+ datasetfilter_dbset + '&polyid='+polyid;
        $("#graphview").load(url);     
}

function featureclicked (e)
{
  //filter line graph to show just data for the dataset for this polygon
    showchartresult(e.layer.feature.properties.polyid);

}



function zoombox(boxres) {
var southWest = L.latLng(boxres.box.ymin, boxres.box.xmin);
 var northEast = L.latLng(boxres.box.ymax, boxres.box.xmax);
 //console.log(boxres);
 bounds = L.latLngBounds(southWest, northEast);
 map.fitBounds(bounds);

}

function getBoundingBox() {

    var projectid = <?php echo ($projectid) ?>;

				    var boxdata='projectid=' + projectid;
				    $.ajax({
				        url: 'getprojectmapextent.php',
				        dataType: 'json',
				        data: boxdata,
				        success: zoombox
				    });
	}


    function getdata(taskid) 
    {         
         //GET only POLYGONS FROM SPECIFIED PROJECT ID  <<<<<<<<<<<<<<<<<<<<<<

          $.ajax({
              url: 'getmapdata.php',
              async:false,
              cache:false,
              timeout:30000,
              data:{ projectid: '<?php echo($projectid) ?>' , pcount:'<?php echo($polycount) ?>' },
              dataType: 'json',
              success: function(data){
                $(data.features).each(function(key, data) 
                     {
                         alertzones.addData(data);
                         alertzones.on({click: featureclicked });
                    });
          }
          });
    }
          
    

    function getmarkers() 
{

        var projectid = <?php echo ($projectid) ?>;

        var dt='projectid='.concat(projectid).concat('&date1=').concat(datasetfilter_date).concat('&dbset=').concat(datasetfilter_dbset); 


        //GET only FIRE POINTS FROM SPECIFIED DATE <<<<<<<<<<<<<<<<<<<<<<        
          $.ajax({
              url: 'getmapdatafirepoints.php',
              async:false,
              cache:false,
              timeout:30000,
              data:dt,
              dataType: 'json',
              success: function(data){
                $(data.features).each(function(key, data) 
                     {

                        var lat = data.properties['latitude'];
                        var lng = data.properties['longitude'];
                        var a1 = 'd:'+data.properties['acq_date'] + '  t:' + data.properties['acq_time'] + '  conf:' + data.properties['confidence']   ;
                        
			            var marker = L.marker(L.latLng(lat,lng), { att: a1 });
			            marker.bindPopup(a1);
			            markers.addLayer(marker);

                    });
          }
          });
    }



//on date change event clear and re-load map data
$('#datepicker').datepicker({ dateFormat: 'yy-mm-dd' })
    .on("input change", function (e) {
        markers.clearLayers();
        showStatus();
        datasetfilter_date = e.target.value;  //save the active date as variable
        getmarkers();
        hideStatus();
       
});


//on dataset event change then clear and re-load map data
$('.dbselector').change(function(){
  var data= $(this).val();
   showStatus();
   datasetfilter_dbset = data; //save the active dataset as variable
   markers.clearLayers();
   getmarkers();
   hideStatus();
   showchartresult('all');
  //alert(data);            
});

$('.dbselector')
    .trigger('change');



//show or hide the LOADING DATA... (Status) message
function showStatus() {
var mySpan = document.getElementById('status');
  mySpan.style.display = "";
}

function hideStatus() {
var mySpan = document.getElementById('status');
  mySpan.style.display = "none";
}


// load the map layer and populate with database polygons for specified task id
alertzones.addTo(map);

//zoom to region
getBoundingBox();

//get the zones data 
getdata();

//default to hide status after data loaded
hideStatus();
</script>



<style>
  #graphview { width: 600px; height: 290px; padding: 0.5em; top:300px; left: 50px;}
</style>


  <script>
  $( function() {
    $( "#graphview" ).draggable();
  } );
  </script>
 
<div id="graphview" class="ui-widget-content" style="z-index: 499;background-color:rgba(10, 10, 10, 0.8)" > 
<br> Note: This window is draggable
</div>



</body>
</html>

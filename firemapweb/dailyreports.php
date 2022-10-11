<!DOCTYPE html>
<html>
<head>

<?php
include('header_alerts.php');

$userid=$_SESSION['usercode'];
$projectid=$_GET['projectid'];

$dateselected=null;


if(isset($_GET['dt'])) {
    $dateselected=$_GET['dt'];
}

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
	
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

    $sql="select project.projectid,project.active,project.projectname,project.notification_emailaddress from project join userproject on project.projectid = userproject.projectid where project.projectid=?  and userproject.userid= ?  ;";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([$projectid, $userid]);	
    $resultcount = $stmt -> rowCount() ;
    $row = $stmt->fetch();
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



</head>
<body>


<h3>Daily Report History for project: <?php echo($projectid);?>
<?php
if ($dateselected) {
  echo (" on ".$dateselected);
} 
?>
</h3> 


<span id='datepicker'> 
    <select class='dbselector' name="dbset"  style="
        position: fixed;
        top: 60px;
        left: 580px;
        z-index: 499;">
        
    <?php 
        $pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

        $sql="select distinct acq_date from public.dailyreporthistory where projectid=$projectid order by acq_date desc limit 15";
        $result = $pdo->prepare($sql);
        $result->execute();	
        $c = $result ->rowCount();

        while ($row = $result->fetch() ) 
        {
            echo "<option value=' " . $row['acq_date'] . " '>".$row['acq_date']."</option>";
        }

        $pdo = null;

    ?>
    </select>

</span>

<div id='map'></div>

<script>

var alertzones = new L.geoJson();
var markers = L.markerClusterGroup();


    var osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            osm = L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttrib }),
            map = new L.Map('map', { center: new L.LatLng(12.0,10.0), zoom: 3 }),
            drawnItems = L.featureGroup().addTo(map);

    L.control.layers({
        'osm': osm.addTo(map),
        "google": L.tileLayer('https://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}', {
            attribution: 'google'
        })
    }, { 'monitor zones': alertzones }, { position: 'topleft', collapsed: false }).addTo(map);


    map.addLayer(markers);




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


function getdata() 
    {         
         //GET only POLYGONS FROM SPECIFIED PROJECT ID  <<<<<<<<<<<<<<<<<<<<<<

         dtdata = "'".concat(datasetfilter_dbset.replace(/\s+/g, '')).concat("'") ;

         alertzones.clearLayers();

          $.ajax({
              url: 'getmapdatadailyhistorypolygons.php',
              async:false,
              cache:false,
              timeout:30000,
              data:{ projectid: '<?php echo($projectid) ?>' , pcount:'<?php echo($polycount)?>', dt:dtdata },
              dataType: 'json',
              success: function(data){
                $(data.features).each(function(key, data) 
                     {
                         alertzones.addData(data);
                    });
          }
          });
    }
    

function getmarkers() 
  {

        var projectid = <?php echo ($projectid) ?>;
        dtdata = "'".concat(datasetfilter_dbset.replace(/\s+/g, '')).concat("'") ;

        var data='projectid='.concat(projectid).concat('&dbset=').concat(dtdata) ; 

        //GET only FIRE POINTS FROM SPECIFIED DATE <<<<<<<<<<<<<<<<<<<<<<        
          $.ajax({
              url: 'getmapdatadailyhistoryfirepoints.php',
              async:false,
              cache:false,
              timeout:30000,
              data:data,
              dataType: 'json',
              success: function(data){
                $(data.features).each(function(key, data) 
                     {

                        var lat = data.properties['latitude'];
                        var lng = data.properties['longitude'];
                        var a1 = 'd:'+data.properties['acq_date'] + '  t:' + data.properties['acq_time'] + ' conf:' + data.properties['confidence'] + ' source:' + data.properties['source']   ;
                        
			            var marker = L.marker(L.latLng(lat,lng), { att: a1 });
			            marker.bindPopup(a1);
			            markers.addLayer(marker);

                    });
          }
          });
    }


function hideDateDropDown() {
var mySpan = document.getElementById('datepicker');
  mySpan.style.display = "none";
}

//=======================================================================================


//on dataset event change then clear and re-load map data
$('.dbselector').change(function(){
  var data= $(this).val();

   datasetfilter_dbset = data; //save the active dataset as variable
   markers.clearLayers();

   //get the zones data 
   getdata();

   //get fire points data
   getmarkers();


});


var datesent = <?php echo (isset($dateselected)) ?  $dateselected : 'null';?>;


if (datesent === null)
{
    //pick up the current date selected from dropdown choice as none sent over in GET request
    $('.dbselector')
        .trigger('change');

       
}
else 
{ 
    //don't show the date picker as date supplied in URL
    hideDateDropDown();

    console.log(datesent);

    datasetfilter_dbset =datesent;

   //get the zones data 
   getdata();

   //get fire points data
   getmarkers();

}


// load the map layer and populate with database polygons for specified task id
alertzones.addTo(map);

//zoom to region
getBoundingBox();


</script>


</body>
</html>
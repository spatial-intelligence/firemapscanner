<!DOCTYPE html>
<html>
<head>
<title>Edit Project Details</title>

<script src="libs/jquery-1.11.1.min.js"></script>

<?php
	include('header_alerts.php');
?>

<?php
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
    echo "You do not have permission to view this record";
    die;

  }


?>



<script>
function checkEnter(e){
 e = e || event;
 var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
 return txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
}
</script>


<meta charset=utf-8 />

    <meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />


        <title>Leaflet.draw vector editing handlers</title>
    
        <script src="libs/leaflet-src.js"></script>
        <link rel="stylesheet" href="libs/leaflet.css"/>
    
        <script src="libs/src/Leaflet.draw.js"></script>
        <script src="libs/src/Leaflet.Draw.Event.js"></script>
        <link rel="stylesheet" href="libs/src/leaflet.draw.css"/>
    
        <script src="libs//src/Toolbar.js"></script>
        <script src="libs//src/Tooltip.js"></script>
    
        <script src="libs/src/ext/GeometryUtil.js"></script>
        <script src="libs/src/ext/LatLngUtil.js"></script>
        <script src="libs/src/ext/LineUtil.Intersect.js"></script>
        <script src="libs/src/ext/Polygon.Intersect.js"></script>
        <script src="libs/src/ext/Polyline.Intersect.js"></script>
        <script src="libs/src/ext/TouchEvents.js"></script>
    
        <script src="libs/src/draw/DrawToolbar.js"></script>
        <script src="libs/src/draw/handler/Draw.Feature.js"></script>
        <script src="libs/src/draw/handler/Draw.SimpleShape.js"></script>
        <script src="libs/src/draw/handler/Draw.Polyline.js"></script>
        <script src="libs/src/draw/handler/Draw.Marker.js"></script>
        <script src="libs/src/draw/handler/Draw.Circle.js"></script>
        <script src="libs/src/draw/handler/Draw.CircleMarker.js"></script>
        <script src="libs/src/draw/handler/Draw.Polygon.js"></script>
        <script src="libs/src/draw/handler/Draw.Rectangle.js"></script>
    
    
        <script src="libs/src/edit/EditToolbar.js"></script>
        <script src="libs/src/edit/handler/EditToolbar.Edit.js"></script>
        <script src="libs/src/edit/handler/EditToolbar.Delete.js"></script>
    
        <script src="libs/src/Control.Draw.js"></script>
    
        <script src="libs/src/edit/handler/Edit.Poly.js"></script>
        <script src="libs/src/edit/handler/Edit.SimpleShape.js"></script>
        <script src="libs/src/edit/handler/Edit.Rectangle.js"></script>
        <script src="libs/src/edit/handler/Edit.Marker.js"></script>
        <script src="libs/src/edit/handler/Edit.CircleMarker.js"></script>
        <script src="libs/src/edit/handler/Edit.Circle.js"></script>


<style>
    body {
        margin: 0;
        padding: 0;
    }
    #map {
        position: absolute;
        top: 1;
        width:100%;
        height: 60%;

    }
    #delete, #export {
        position: absolute;
        top:50px;
        right:10px;
        z-index:500;
        background:white;
        color:black;
        padding:6px;
        border-radius:4px;
        font-family: 'Helvetica Neue';
        cursor: pointer;
        font-size:12px;
        text-decoration:none;
    }
    #export {
        top:300px;
        left: 10px;
    }

</style>

<script src="libs/jquery-1.11.1.min.js"></script>



</head>
<body>
<script>
function validateForm() {

    exporttoDB(); //save the map data using function from the mapedit.php page

 if (details.projectname.value == null || details.projectname.value.length < 2) {
        alert("Please supply an project name");
        return false;
    };

if (details.notification_emailaddress.value == null || details.notification_emailaddress.value.length < 6) {
        alert("Please supply an contact email address");
        return false;
    };
 

    location.reload();
}

</script>
<h1>EDIT Project: <?php echo($projectid) ?></h1>

<div class="content">
    <form id="details" action="update_project.php" onsubmit="return validateForm()" method="POST">

    <script>document.querySelector('form').onkeypress = checkEnter;</script>

        <div id="choice">

            <input type='hidden' value='0' name='active'> 
            <input type='hidden' value=<?php echo($projectid) ?> id = "pid" name='pid'>

            <br><b>Project Name:</b> 
            <input type="text" name="p" id ="p" size="100" value= '<?php echo($row['projectname'])?> '> <br>

            <br><b>Email address to send notifications:</b>
            <input type="text" name="e" id ="e" size="50"value= '<?php echo($row['notification_emailaddress'])?> ' > <br>

            <br> <b>Active:</b> &nbsp
            <input type="checkbox" name="a" id="a" value="1"  <?php if ($row['active'] == 't') echo ("checked='checked'");?>><br><br><br>

            <input name="mySubmit" type="submit"  value="Update Record on Server"/>
            <br><br>
            
        </div> <br><br>

       

    </form>


    <br><br>
</div>  

<script>
        function makeFormReadOnly() {
                details.hidden=true;
                map.hidden = true;
                details.active.readOnly=true;
                details.pid.readOnly=true;
                details.p.readOnly=true;
                details.e.readOnly=true;
                details.a.hidden=true;   

                details.mySubmit.hidden = true;
            }

</script>



<div id='map'></div>


<script>

var alertzones = new L.geoJson();


    var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            osmAttrib = '&copy; <a href="http://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            osm = L.tileLayer(osmUrl, { maxZoom: 18, attribution: osmAttrib }),
            map = new L.Map('map', { center: new L.LatLng(51.505, -0.04), zoom: 13 }),
            drawnItems = L.featureGroup().addTo(map);

    L.control.layers({
        'osm': osm.addTo(map),
        "google": L.tileLayer('http://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}', {
            attribution: 'google'
        })
    }, { 'drawlayer': alertzones }, { position: 'topleft', collapsed: false }).addTo(map);

   

    var drawControl = new L.Control.Draw({
            edit: {
                featureGroup: alertzones,
                 polygon: { allowIntersection: false , showArea: true}
            },
             draw: {
			            polygon: {
			                allowIntersection: false,
			                showArea: true
			            },

            circle:false,
			circlemarker: false,
            marker: false,
            rectangle: false,
            polyline: false
        }
        }).addTo(map);


    map.on(L.Draw.Event.CREATED, function (event) {
        var layer = event.layer;

        alertzones.addLayer(layer);


    });


function featureclicked (e)
{
    alert (e.layer.feature.properties.polyid);
    alert (e.layer.feature.properties.projectid);


}



function zoombox(boxres) {
var southWest = L.latLng(boxres.box.ymin, boxres.box.xmin);
 var northEast = L.latLng(boxres.box.ymax, boxres.box.xmax);
 console.log(boxres);
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
         
         //GET only POLYGONS FROM SPECIFIED PROJECT ID  
         var projectid = <?php echo ($projectid) ?>;
         var pid='projectid='.concat(projectid);

          $.ajax({
              url: 'getmapdata.php',
              async:false,
              cache:false,
              timeout:30000,
              data:pid,
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
          
    


    function exporttoDB(){

            var data = alertzones.toGeoJSON();

            // Stringify the GeoJson
            var convertedData = encodeURIComponent(JSON.stringify(data));
            var projectid = <?php echo ($projectid) ?>;


                                //SEND TO DATABASE
                          var dataString = 'd='+ convertedData + '&p='+projectid;
                          $.ajax({
                            type:'POST',
                            data:dataString,
                            url:'pg_insert_mapdata.php'
                          });
}





// load the map layer and populate with database polygons for specified task id
alertzones.addTo(map);
getdata();
getBoundingBox();


</script>


      


</body>
</html>
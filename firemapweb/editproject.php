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
        bottom: 0;
        width:100%;
        top: 340px;
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

    //only run MAP export if polygon count <30
    var polycount = <?php echo ($polycount) ?>;

    var countzonesmaplayer = countMapFeatures();

    if (countzonesmaplayer > 249 )  // to prevent slow saves and loads, and data simplificatino on display being overwritten back to DB
    {
        alert ("Sorry, map data edits/additions NOT being saved back to Database - #polygon count too high")
    }
    else
    {
       exporttoDB(); //save the map data using function from the mapedit.php page
    }


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
            
        </div> <br>

       

    </form>

 

   <div class = 'mapfeatures_count'> #Map Features: <span  id = 'mapfeaturecount'>  </span>

   <?php
if ($polycount >250) {
    echo "<p style='color:red'>&nbsp;High polygon count [".$polycount."] - edit tools disabled and map view simplified for display purposes.</p>";
}
?>
   </div> 



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


    // only show edit tools if the number of polygons is below 250

    var polycount = <?php echo ($polycount) ?>;

    if (polycount <250) {

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

    }



    map.on(L.Draw.Event.CREATED, function (event) {
        var layer = event.layer;
        alertzones.addLayer(layer);
    });

    map.on("move", function(event) { 
              document.getElementById("mapfeaturecount").innerHTML=countMapFeatures(); 
         });


function countMapFeatures ()
    {
        var counter = 0;
        map.eachLayer((layer)=>{
        if(layer instanceof L.Polygon){
            counter++;
        }
        });

    return counter;

    }


function featureclicked (e)
{
    alert (e.layer.feature.properties.polyid);
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
          $.ajax({
              url: 'getmapdata.php',
              async:false,
              cache:false,
              timeout:30000,
              data: { projectid: '<?php echo($projectid) ?>' , pcount:'<?php echo($polycount) ?>' },
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


function exporttoDB()
    {
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

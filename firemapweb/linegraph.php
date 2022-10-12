
<!DOCTYPE html>
<html>

	<head>

	<?php
	   $dbset =  $_GET['dbset']; 
	   $polyid =  $_GET['polyid']; 

	?>

		<title>LineGraph</title>
		<style>
			.chart-container {
				width: 600px;
				height: 290px;
			}
		</style>
	</head>
	<body>
		<div class="chart-container">
			<canvas id="chart1"></canvas>

		</div>
	
		<!-- javascript -->
		<script src="libs/jquery-1.11.1.min.js"></script>
		<script type="text/javascript" src="libs/Chart.min.js"></script>
		<script type="text/javascript" src="linegraph.js"> </script>	

<?php

	if 	($polyid == 'all')
	{
		echo ( '<br> Dataset: '.$dbset) ;
	}
	else {
		echo ('<br> Polygon: '.$polyid );
	}
?>

	<script>

		$(document).ready(function(){
			loaddata('<?php echo ($dbset) ?>', '<?php echo ($polyid) ?>' );
		});
        

    </script>
	
</body>
</html>

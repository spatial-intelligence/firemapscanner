
function loaddata(dbset)
{
	var LineGraph ;
	
$.ajax({
		

		url : "linedata.php?dbset="+dbset,
		type : "GET",
		success : function(data){
			//console.log(data);
			var json =  jQuery.parseJSON(data);

			var x = [];
			var T = [];
	
	
			for(var i in json) {
				x.push(json[i].dt);
				T.push(json[i].c);
	
			}

			var chartdata = {
				labels: x,
				datasets: [
					{
						label: "Dataset Daily Counts",
						fill: false,
						yAxisID: 'A',
						backgroundColor: "rgba(200, 89, 152, 0.5)",
						borderColor: "rgba(200, 89, 152, 0.5)",
						data: T
					}
					
					
				]
			};


			var ctx = $("#chart1");

			 LineGraph = new Chart(ctx, {
				type: 'line',
				data: chartdata,
				
		options: {
			//onClick: graphClickEvent,   // to link from clicking on graph - disabled

			legend:{
				labels :{
					boxWidth : 8
				}},
			
			elements: 
			{
				line: 
				{
					tension: 0, // disables bezier curves
				}
			},
			animation: 
			{
					duration: 0 // general animation time
			},
			scales:
			{
			
					xAxes: [{
					ticks: {
						autoSkip:true,
						maxRotation: 45,
					 	minRotation:45
				        } 
					}],
					
                    yAxes: [{
						id: 'A',
						type: 'linear',
						position: 'left',
						ticks: {
							beginAtZero:true
					}}]
				
			}//end scales
				
				
				}//end options
			});
		},
		error : function(data) {
			console.log('chart error');
		}
		
	});



	function graphClickEvent(event, array){

		  var activeElement = LineGraph.getElementAtEvent(event);

			var clickedDatasetIndex = activeElement[0]._datasetIndex;
			var clickedElementindex = activeElement[0]._index;
			var label = LineGraph.data.labels[clickedElementindex];
			var value = LineGraph.data.datasets[clickedDatasetIndex].data[clickedElementindex];     

			datasetfilter_dbset = label.split(' ')[0]; //save the active dataset as variable
   		

		}

	
	
}

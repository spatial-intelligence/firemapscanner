$( document ).ready(function() {
  $('#editableTable').SetEditable({
	  columnsEd: "1,2,3",
	  onEdit: function(columnsEd) {
		 // console.log("===edit=="+(this));
		var projectid = columnsEd[0].childNodes[1].innerHTML;
        var projectname = columnsEd[0].childNodes[3].innerHTML;
        var startdate = columnsEd[0].childNodes[5].innerHTML;
        var active = columnsEd[0].childNodes[7].innerHTML;
		$.ajax({
			type: 'POST',			
			url : "action.php",	
			dataType: "json",					
			data: {projectid:projectid, projectname:projectname, startdate:startdate, active:active, action:'edit'},			
			success: function (response) {
				if(response.status) {
					// show update message
					alert (response.status);
				}						
			}
		});
	  },
	  onBeforeDelete: function(columnsEd) {
	  var projectid = columnsEd[0].childNodes[1].innerHTML;
	  $.ajax({
			type: 'POST',			
			url : "action.php",
			dataType: "json",					
			data: {projectid:projectid, action:'delete'},			
			success: function (response) {
				if(response.status) {
					// show delete message
				}			
			}
		});
	  },
	});
});
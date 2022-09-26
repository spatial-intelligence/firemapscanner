<!DOCTYPE html>
<html>
<head>
<title>Add New Project</title>

<script src="libs/jquery-1.11.1.min.js"></script>

<?php
	include('header_alerts.php');
  
?>

<h1>Add Project:</h1>
<h3>  User: <?php echo(strtoupper($userid)) ?>  </h3>

</head>
<body>

<script>

var chk_stid='';
var chk_stid_free=null;

function checkProjectCode() {

projectid= details.p.value;


		var data='projectid='.concat(projectid);

		$.ajax({ url: 'checkprojectexists.php',
    		dataType: 'json',
    		data: data,
    		success: function(output) {
        		chk_stid=output['projectname'];
       			document.getElementById('pcode').innerHTML = chk_stid ;

    		}
 		});
}




function validateForm() {

    if (details.t.value == null || details.t.value == "") {
        alert("Please supply a project name");
        return false;
    };

 if (details.a.value == null || details.a.value.length < 3) {
        alert("Please supply an abstract");
        return false;
    };

}

</script>


<div class="content">
    <form id="details" action="submit_newproject.php" onsubmit="return validateForm()" method="POST">

  
        <br>
        <div id="choice">
                <h3>Project Details</h3>
                <br>
                    <label><b>Project Number:</b></label>
                    <input type ="number" placeholder="enter an integer" min=1 name="p" size="6" onchange="checkProjectCode()" /> <div id="pcode"></div>
                    <br>	
                    <label><b>Project Name:</b></label>
                    <input type ="text" name="t" size= "80"/><br>
            <br>
            <!-- default to NOT active but user can tag as active -->
            <input type='hidden' value='0' name='c_active'>   
            <input type='hidden' value='<?php echo($userid) ?>' name='userid'>
            <br>
            <label><b>Active (monitoring):</b></label> <input type="checkbox" name="c_active" id ="1" value="1" >
            <br>  <br>

            </div> <br><br>
                    
        <input name="mySubmit" type="submit"  value="Add Project" />
       
    </form>
</div>

<?php

    $username = getenv('DBFIRE_USERNAME');
    $password = getenv('DBFIRE_PASSWORD');
            
    $pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);
            
    $sql="select coalesce(1+max(projectid),1) as nbr from project where owner_userid = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userid]);	

    $rows = $stmt->fetch() ;

    $row = $rows["nbr"];

       echo ("<script>details.p.value= '$row' </script>") ;

    $pdo = null;

?>



</body>
</html>
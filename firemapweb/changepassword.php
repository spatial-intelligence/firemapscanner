<!DOCTYPE html>
<html>
<head>
<title>Change Your Password</title>
<link rel="stylesheet" href="style.css" />
<script src="libs/jquery-1.11.1.min.js"></script>



<?php
include('header_alerts.php');
$userid=$_SESSION['usercode'];

?>


<script>
function checkEnter(e){
 e = e || event;
 var txtArea = /textarea/i.test((e.target || e.srcElement).tagName);
 return txtArea || (e.keyCode || e.which || e.charCode || 0) !== 13;
}
</script>
</head>
<body>

<script>
var authenticated=0;

function checkPIN() {

      var s='<?php echo ($userid) ?>';
	    var a=details.oldpw.value;
		var data='s='.concat(s).concat('&p=').concat(a);

		$.ajax({ url: 'passwordcheck.php',
    		dataType: 'json',
    		data: data,
    		success: function(output) {
        		check=output["count"];
               


   		    if (check==1){
				        			$("#validate").html("<b class='confirm'>PASSWORD OK :-)</b>");
				        			authenticated=1;
				        		}
			else
				        		{
				        		$("#validate").html("<b class='alert'>Wrong PASSWORD!</b>");
				        		   authenticated=0;
				        		}
				        }
        		});
}





function validateForm() {


    if (details.at1.value == null || details.at1.value == "") {
        alert("NEW PASSWORD must be filled out");
        return false;
    };

  if (details.at1.value != details.at2.value) {
        alert("NEW PASSWORD values are not the same!");
        return false;
    }



checkPIN();

if (authenticated==0 )
{
alert ("PASSWORD is wrong!");
return false;
}


}


</script>

<h1>Change Your PASSWORD:<?php echo(strtoupper($userid)) ?></h1>

<div class="content">
    <form id="details" action="updatepassword.php" onsubmit="return validateForm()" method="POST">

<script>document.querySelector('form').onkeypress = checkEnter;</script>

    <div id="auth">
<br>

	 <label>CURRENT PASSWORD</label>
        <input name="oldpw" id='oldpw'  type="password" onchange="checkPIN()" size="15"/><div id="validate"></div>

<br><br>
    <label>Enter NEW PASSWORD</label>
        <input name="at1" id='at1' min="1" type="password" size="25"/>
       &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp

   <label>Re-enter NEW PASSWORD</label>
        <input name="at2"  min="1" type="password" p size="25"/>
<br>


  <input type='hidden' value=<?php echo ($userid) ?> name='userid'>
<br><br>
<br>
        <input name="mySubmit" type="submit"  value="Change PASSWORD" />
        </form>
</div>
</body>
</html>
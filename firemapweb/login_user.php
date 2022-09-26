<title>Project Choices</title>
<link rel="stylesheet" href="style.css" />
<?php

 if(isset($_POST['usercode']))
{
	if (strlen($_POST['usercode'])  > 0)
	{

			$s=$_POST['usercode'];
			$a=$_POST['password'];

			$phash= hash('sha256', $a);

			try 
			{
			
				// env variables are set in /etc/apache2/envvars
				$username = getenv('DBFIRE_USERNAME');
				$password = getenv('DBFIRE_PASSWORD');
			
				$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);
		
				$sql="select userid,password from users where userid = ? and password =? and (select count(*) from interaction_history where sid=-901 and value= ?) < 9 ;";
				$stmt = $pdo->prepare($sql);

				$stmt->execute([$s,$phash, $s]);	

				$c = $stmt ->rowCount();

				$pdo = null;

			} catch (PDOException $e) 
			{
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
	
			if ($c > 0)
			{

				$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

				$stmt = $pdo->prepare($sql);

				$stmt->execute([$s]);	
				$pdo = null;

					session_start();
					   $_SESSION['usercode']=$_POST['usercode'];
					header("location: projectlist_you.php");
			}
			else 
			{
				$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);

				  $sql= "INSERT into interaction_history (sid,datetime,action,value) values (-901,now(),'login-failed',?);";
				  $stmt = $pdo->prepare($sql);
				  $stmt->execute([$s]);	

				  $stmt =null;

				  $sql2= "select * from interaction_history where sid=-901 and value= ? ;";
				  $stmt2 = $pdo->prepare($sql2);
				  $stmt2->execute([$s]);	

				  $fl = $stmt2 ->rowCount();

				  $pdo = null;

			if ($fl >= 9 )
			{
  				echo('<br><br><h3>==>> Account has been locked due to number of failed log in attempts. <br>==> <i>Contact OSR4Rights team to get account unlocked.</i></h3>');
			}
			else
			{
				  echo('<br><br><h3> ==>> Login Failed: Check password and try again.</h3>');
			}

			}

	}
}


?>


<html>
	<head>
		<title>Log in to the OSR4Rights Fire Alert System</title>
		</head>
		<body>
<h1> Please Log in to the OSR4Rights Fire Alert System</h1>
<div id="loginBox">
			<form action="" method="post" id="details">
			<div><label> User ID: </label><input type="text" name="usercode" size="20" placeholder="Type in your User ID"></div>
			<div><label> Authorisation Code:</label>	<input type="password" min = "1" name="password" size="30"  placeholder="Type in your Authentication Password"></div>
			<input type="submit" value="Log in" class="btn">
			</form><br><br>

</div>


		</body>
</html>

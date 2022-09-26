<?php
	session_start();
	if(!isset($_SESSION['usercode']))
	{
		header("location: login_user.php");
		die();
	}
	$userid=$_SESSION['usercode'];
?>

<link rel="stylesheet" href="style.css" />
<p style = "font-size: 75%"> Menu: [Logged in as: <?php echo $userid;?>]&nbsp&nbsp&nbsp&nbsp<a href="projectlist_you.php">[Your Projects]</a>&nbsp&nbsp&nbsp&nbsp<a href="addproject.php">[Add Project]</a>&nbsp&nbsp&nbsp&nbsp<a href="changepassword.php">[Change Password]</a>&nbsp&nbsp&nbsp&nbsp<a href="logout.php">[Log out]</a>
</p>



<?php

echo ("<!DOCTYPE html>");

include('header_alerts.php');

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');

$userid=$_SESSION['usercode'];
$projectid=$_GET['projectid'];


$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);
$sql='select distinct acq_date from public.dailyreporthistory join userproject on dailyreporthistory.projectid=userproject.projectid where dailyreporthistory.projectid=? and userid=? order by acq_date desc;';

$result = $pdo->prepare($sql);
$result->execute([$projectid,$userid]);

echo '<h3> Daily Reports for Project:'.$projectid.' </h3> ';
$date = new DateTime();
echo $date->format('d/m/Y (H:i:s)');
echo ("<br>");

echo '<table id="dailytable" >';
echo '<tr><th style="width: 85px">Daily Reports <br>(yyyy-mm-dd)</th><th style ="width:420px">';

$lastgrp=False;

       while ($rows = $result->fetch() ) 
		
		{
		if ($lastgrp==True)
		{
			echo ('<tr class="alt">');
		}
		else
		{
			echo ('<tr>');
		}

$lastgrp = !$lastgrp;

	    echo ('<td valign="top">');

		echo ($rows["acq_date"]);

	    echo ('</td><td valign="top" >');

		echo ("<a href=dailyreports.php?projectid=".$projectid."&dt='".$rows["acq_date"]."'  >[daily map]</a> &nbsp;");

		echo ("</td></tr>");
		}

echo ("</table>");

$pdo = null;

?>
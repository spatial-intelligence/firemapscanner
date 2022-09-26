<?php

echo ("<!DOCTYPE html>");

include('header_alerts.php');

$username = getenv('DBFIRE_USERNAME');
$password = getenv('DBFIRE_PASSWORD');
		
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $username, $password);
$sql=" select *,(owner_userid=?) as owneridcheck from project join userproject on project.projectid = userproject.projectid where userproject.userid= ? order by active desc, project.projectid,startdate desc";
$result = $pdo->prepare($sql);
$result->execute([$userid,$userid]);

echo '<h3> Your Project List</h3> ';
$date = new DateTime();
echo $date->format('d/m/Y (H:i:s)');
echo ("<br>");

echo '<table id="projecttable">';
echo '<tr><th style="width: 90px">Project ID </th><th style ="width:450px">Project Name </th><th>Date Created </th><th>Owner UserID</th><th style ="width:120px">Active Status</th><th style="width:350px">Links</th></tr>';

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

		echo ($rows["projectid"]);
		echo ("<span id='".$rows["projectid"]."'></div>");
	    echo ('</td><td valign="top" >');
	    echo (html_entity_decode($rows["projectname"]));
	    echo ('</td><td valign="top" >');
		echo (html_entity_decode($rows["startdate"]));
		echo ('</td><td valign="top" >');
        echo (html_entity_decode($rows["owner_userid"]));
		echo ('</td>');

		$active=$rows["active"];

		if ($active=='t')
		{
		   $projectactive='active';
		   echo ('<td valign="top" style="color:black">'.$projectactive.'<br><br>');
		}
        else{
            $projectactive = 'not active';
			echo ('<td valign="top" style="color:green">'.$projectactive.'<br><br>');

        }

		

		echo ('</td><td valign="top">');
		echo ('<a href=editproject.php?projectid='.$rows["projectid"].'>[edit]</a> &nbsp;');

		echo ('<a href=uploadfile.php?projectid='.$rows["projectid"].' ">[upload]</a> &nbsp;');

		echo ('<a href=historycheckphp?projectid='.$rows["projectid"].' ">[historic events]</a> &nbsp;');



		//only owner can delete a project - not anyone else with edit permissions
		$ownercheck=$rows["owneridcheck"];
		if ($ownercheck=='t')
		{
			echo ('<a href=removeproject.php?projectid='.$rows["projectid"].' onclick="return confirm (\'Are you sure you want to DELETE the project?\')">[delete]</a>');

		}

		


		echo ("</td></tr>");
		}

echo ("</table>");

$pdo = null;


?>
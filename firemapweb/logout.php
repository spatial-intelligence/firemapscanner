<?php

					session_start(); # NOTE THE SESSION START
					$_SESSION = array();
					session_unset();
					session_destroy();

					header("Location: login_user.php");
					exit(); # NOTE THE EXIT


echo "Logged Out!";
?>

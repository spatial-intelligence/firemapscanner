<?php

// Development or prod? 
$webserver = getenv('WEBSERVER');
if ($webserver != "azureprod") {

	// Development
	session_start();
	if (!isset($_SESSION['usercode'])) {
		header("location: login_user.php");
		die();
	}
	$userid = $_SESSION['usercode'];
?>
	<link rel="stylesheet" href="style.css" />
	<p style="font-size: 75%"> Menu: [Logged in as: <?php echo $userid; ?>]&nbsp&nbsp&nbsp&nbsp<a href="projectlist_you.php">[Your Projects]</a>&nbsp&nbsp&nbsp&nbsp<a href="addproject.php">[Add Project]</a>&nbsp&nbsp&nbsp&nbsp<a href="changepassword.php">[Change Password]</a>&nbsp&nbsp&nbsp&nbsp<a href="logout.php">[Log out]</a>
	</p>
<?
// development - stop script here
die();
}




// DM all new code below to handle authentication from .NET
// Essentially reads the .NET authentication cookie
// then queries the mssql db to check the cookie is there
// and gets the userId and email back from the DB
// we could have read the cookie as have access the machine key
// to decrypt it, but chose not to.

// Azure production
function exception_handler($exception)
{
	echo "<h1>Failure</h1>";
	echo "Exception: ", $exception->getMessage();
	echo "<h1>PHP Info for troubleshooting</h1>";
	phpinfo();
}
# default exception handler if not within a try/catch
# eg when database is down or wrong connection string
set_exception_handler('exception_handler');

function formatErrors($errors)
{
	echo "<h1>SQL Error:</h1>";
	echo "Error information: <br/>";
	foreach ($errors as $error) {
		echo "SQLSTATE: " . $error['SQLSTATE'] . "<br/>";
		echo "Code: " . $error['code'] . "<br/>";
		echo "Message: " . $error['message'] . "<br/>";
	}
}

// Get .NET Cookie 
$cookie_name = "_AspNetCore_Cookies";

if (!isset($_COOKIE[$cookie_name])) {
	// No .NET cookie so redirect to login page on .NET side with a return url

	header('Location: /account/login');
	// header('Location: /');
	die();
} else {

	// we always need to check the .NET cookie even though a PHP cookie is there
	// as it is the canonical version of the truth
	// ie if the user logs out on the .NET side, or changes to a different user
	// then the PHP should follow
	session_start();

	$cookie_value = $_COOKIE[$cookie_name];

	//
	// Call mssql database to see if this is a real cookie that we know about
	//

	// env variables are set in /etc/apache2/envvars
	// which are copied in the deploy script to the server 
	$serverName = getenv('DB_SERVERNAME');
	$database = getenv('DB_DATABASE');
	$username = getenv('DB_USERNAME');
	$password = getenv('DB_PASSWORD');

	$connectionOptions = array(
		"database" => $database,
		"uid" => $username,
		"pwd" => $password,
		"TrustServerCertificate" => true
	);

	// Establishes mssql db connection
	$conn = sqlsrv_connect($serverName, $connectionOptions);
	if ($conn === false) {
		// unfriendly error here to user..
		// but can't access mssql db
		// todo make more friendly
		die(formatErrors(sqlsrv_errors()));
	}

	$tsql = "
	SELECT c.LoginId, l.Email 
	FROM Cookie c 
	JOIN Login l on c.LoginId = l.LoginId
	WHERE CookieValue = ? 
	AND ExpiresUtc > GETUTCDATE()";

	$params = array($cookie_value);
	$stmt = sqlsrv_query($conn, $tsql, $params, array("Scrollable" => 'static'));

	if ($stmt === false) {
		// database query problem
		// again we are dying
		// need to make more friendly
		die(formatErrors(sqlsrv_errors()));
	}

	$row_count = sqlsrv_num_rows($stmt);

	if ($row_count == 0) {
		echo "unusual - cookie problem in the db - not authenticated!";
		echo "can't do a redirect as displaying this error!";
		die();
	} else {
		// success - assume only 1 row
		// echo "authenticated - but not authorised yet ie could be tier1,2,or admin";

		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$loginId = $row['LoginId'];
			$email = $row['Email'];

			// eg loginId 5 is davemateer@gmail.com from mssql side
			// so use that on this PHP side
			$_SESSION['usercode'] = $loginId;
			$_SESSION['email'] = $email;

			// make sure the postgres users table is populated with this user 
			$pg_username = getenv('DBFIRE_USERNAME');
			$pg_password = getenv('DBFIRE_PASSWORD');
			$pg_pdo = new PDO('pgsql:host=127.0.0.1;dbname=nasafiremap', $pg_username, $pg_password);

			$pg_sql = "INSERT into users (userid,password) values (?,'a');";
			$pg_stmt = $pg_pdo->prepare($pg_sql);
			$pg_stmt->execute([$loginId]);

			// don't care if fails as that means the user is already there
		}
	}

	sqlsrv_free_stmt($stmt);
	sqlsrv_close($conn);
}

// PB uses this in code as include is called on every private page
$userid = $_SESSION['usercode'];
?>

<link rel="stylesheet" href="style.css" />
<p style="font-size: 75%"> Menu: [Logged in as: <?php echo $userid; ?> - <?php echo $_SESSION['email']; ?>]&nbsp&nbsp&nbsp&nbsp<a href="projectlist_you.php">[Your Projects]</a>&nbsp&nbsp&nbsp&nbsp<a href="addproject.php">[Add Project]</a>&nbsp&nbsp&nbsp&nbsp<a href="/">[Home]</a>&nbsp&nbsp&nbsp&nbsp<a href="/account/logout">[Log out]</a>
</p>

<?php

$auth=$_POST['pw'];

$phash= hash('sha256', $auth);

echo ('Output hash for supplied password: &nbsp;');
echo ($auth);
echo ('<br><br>');
echo ($phash);


?>
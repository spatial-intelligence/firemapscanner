<!DOCTYPE html>
<html>
<head>
<title>Generate Password Hash</title>



<h1>Get Password Hash<?php echo(strtoupper($userid)) ?></h1>

<div class="content">
    <form id="details" action="calchashpassword.php" onsubmit="return validateForm()" method="POST">

<script>document.querySelector('form').onkeypress = checkEnter;</script>

    <div id="auth">
<br>

	 <label> PASSWORD</label>
        <input name="pw" id='pw'  type="password" size="15"/><div id="validate"></div>

<br><br>
<br>
        <input name="mySubmit" type="submit"  value="Get Hash PASSWORD" />
        </form>
</div>
</body>
</html>
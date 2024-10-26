<?php
session_start();
// $_SESSION['allowed'] = 0;
if (!isset($_SESSION['allowed']) or ($_SESSION['allowed'] == 0)) {
	if (isset($_POST['login']) && !empty($_POST['login'])) {
		
		$_SESSION['allowed']=0;
		
		$user="Mischpultmama";
		$pw="mischpultNR1";	
		if (($_POST['user'] == $user) && ($_POST['password'] == $pw)) {
			$_SESSION['allowed']=1;
		}
		
		$user="Jörg";
		$pw="Radio#123";
		if (($_POST['user'] == $user) && ($_POST['password'] == $pw)) {
			$_SESSION['allowed']=1;
		}
	}
}

if (!isset($_SESSION['allowed']) or ($_SESSION['allowed'] == 0)) {
	echo '<html><body>';
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	echo '<table>';
	echo '<tr><th>Login Name</th><td><input type="text" name="user" width="60"></td></tr>';
	echo '<tr><th>Passwort</th><td><input type="password" name="password" width="30"></td></tr>';
	echo '<tr><th>&nbsp;</th><td><input type="submit" value="Login" name="login" width="30"></td></tr>';
	echo '</table>';
	echo '</body></html>';
	exit;
	
}
?>
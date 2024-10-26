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
	
	
include "class/news.class.php";



$n = new news();
$n->load();

if (isset($_POST['add']) && !empty($_POST['add'])) {
	if (isset($_POST['date']) && isset($_POST['text'])) {
		echo "ADD Button gedrückt";
		// $dt = new DateTime($_POST['date']);
		
		// $n->add($dt->format('d.m.Y'),$_POST['text']);
		$n->add($_POST['date'],$_POST['text']);
		$n->save();
	}
} else 
if (isset($_POST['delete']) && !empty($_POST['delete'])) {
	$n->delete($_POST['pos']);
	$n->save();
} else 
if (isset($_POST['edit']) && !empty($_POST['edit'])) {
	$n->displayEdit();
	exit;
} else	
if (isset($_POST['update']) && !empty($_POST['update'])) {
	$n->updateEdit();
	$n->save();
}	
	

$n->display();
	
	
?>

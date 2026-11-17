<?php
session_start();

require_once('Models/User.php');

//Controller that logs the user out, it has no view because it
//handles the action and then redirects the user to users.php.
$userLogin = new User();
$userLogin->userLoggedOut(); //Empties the session data and then destroys the session.

//Redirects the user after logging out, the "exit" stops the script from
//running any further.
header('Location: users.php');
exit;

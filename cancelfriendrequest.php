<?php


session_start();
require_once('Models/FriendDataSet.php');
require_once('Models/User.php');

//Controller that cancels a friend request the logged-in user sent. It has no view of its own
//because it handles the action and then redirects the user to another page.
$userAccount = new User();

//The user is redirected to friends.php by default.
$current_destination = "friends.php";

//If the user is not logged-in, then they are redirected to the login page instead. Otherwise,
//if a request id was submitted into the form, then that request is cancelled. The request id is then cast
//to an integer with (int). The user id comes from the session so that a user can only
//cancel requests that they sent. (Also, the SQL in cancelFriendRequest() checks for this.)
if (!$userAccount->userLoggedIn()) {
    $current_destination = "login.php";
} elseif (isset($_POST["requestId"])) {
    $friendSet = new FriendDataSet();
    $friendSet->cancelFriendRequest((int)$_POST['requestId'], $_SESSION['userId']);
}

//A final redirect at the end with "exit" so that the script
//stops running.
header("Location: " . $current_destination);
exit;


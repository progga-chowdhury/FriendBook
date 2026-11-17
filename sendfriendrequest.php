<?php

session_start();

require_once('Models/FriendDataSet.php');
require_once('Models/User.php');

//Controller that sends a friend request from the logged-in user to a different
//user. It has no view of its own because it handles the action and saves a message for the user, then
//redirects them to users.php
$userAccount = new User();

//The user is redirected to users.php by default.
$current_destination = "users.php";

//If the user isn't logged in, or no receiver id was submitted from the form, they are
//redirected to the login page instead.
if (!$userAccount->userLoggedIn() || !isset($_POST["receiverId"])){
    $current_destination = "login.php";

}

//Otherwise, the user can send the request. The sender id comes from the session so that
//a user cannot send a friend request as someone else, the receiver id is then cast to an
//integer with (int).
else {
    $friendSet = new FriendDataSet();
    $request_sent = $friendSet->sendFriendRequests($_SESSION["userId"], (int)$_POST["receiverId"]);

    //sendFriendRequests() returns false for a request to the user themselves or to a duplicate request, so
    //a different message is saved in the session for each type of result. users.php shows the message once and
    //then removes it.
    if ($request_sent) {
        $_SESSION["message"] = "Friend request sent!";
    }
    else {
        $_SESSION["message"] = "Sorry, can't send that request..!";
    }

}

//A final redirect at the end with "exit" so that the script
//stops running.
header("Location: " . $current_destination);
exit;





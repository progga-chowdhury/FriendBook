<?php

session_start();

require_once("Models/FriendDataSet.php");
require_once("Models/UserDataSet.php");
require_once("Models/User.php");

//Controller for the Friend list page. It gets every friendship/friend request
//involving the logged-in user and sorts them into three lists for the view, incoming
//friend requests, outgoing friend requests and accepted friends.
$userAccount = new User();

//Only logged-in users are able to see this page, otherwise they are directed
//back to the login page.
if (!$userAccount->userLoggedIn()) {
    header("Location: login.php");
    exit;
}

$view = new stdClass();
$view->pageTitle = "My Friends";

$friendSet = new FriendDataSet();
$userSet = new UserDataSet();

//The logged-in user's id comes from the session so that they can only ever see
//their own friends.
$current_user = $_SESSION["userId"];

//The three lists first start empty and are filled in the foreach loop below.
$view->incomingFriendRequests = [];
$view->outgoingFriendRequests = [];
$view->friends = [];

//This loops through every row in the friends table that involves the logged-in user.
foreach ($friendSet->fetchFriendListInfo($current_user) as $row) {
    //Each row has two users, friend1 as the sender and friend2 as the receiver. This page needs to show the
    //"other" person. E.g. the logged-in user is friend1 (the sender), the page would require friend2 as
    //the "other" person, and vice versa.
    if ($row->getFriend1() == $current_user) {
        $otherId = $row->getFriend2();
    } else {
        $otherId = $row->getFriend1();
    }

    //Keeps the request id and the other person's details grouped together.
    $friendEntry = ["requestId" => $row->getId(), "user" => $userSet->fetchUserById($otherId)];

    //These if/else conditions sort everything into the correct list. If status =2, the friendship is accepted.
    //Otherwise, the request is still pending. If the logged-in user is the receiver then it will be
    //displayed as an incoming request, if the logged-in user is the sender then it will be displayed as
    //an outgoing request.
    if ($row->getStatus() == 2) {
        $view->friends[] = $friendEntry;
    } elseif ($row->getFriend2() == $current_user) {
        $view->incomingFriendRequests[] = $friendEntry;
    } else {
        $view->outgoingFriendRequests[] = $friendEntry;
    }
}


require_once("Views/friends.phtml");

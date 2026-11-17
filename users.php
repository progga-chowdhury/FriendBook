<?php
session_start();

require_once('Models/FriendDataSet.php');
require_once('Models/UserDataSet.php');
require_once('Models/User.php');

//Controller for the "Find Friends!" page, it fetches one page of users,
//works out the paging logic, collects the information the view needs, the loads the view.


$view = new stdClass(); //$view holds everything the view file is actually allowed to use.

//object initialization.
$dataSet = new UserDataSet();
$friendSet = new FriendDataSet();
$user = new User();

$view->pageTitle = 'Find friends';

//decides if someone is logged in and the view only displays based on that.
$view->loggedIn = $user->userLoggedIn();
$view->currentUserId = 0; //userId becomes 0 when a user is logged out, so the view always
//contains a value.

if ($view->loggedIn) {
    $view->currentUserId = $_SESSION['userId'];
}

//The logged-in user is left out of the list
$excludedId = $view->currentUserId;

$perPage = 10; //amount of users listed per page.

//This is the search text from the URL. trim() removes any
//spaces, it's bound as a value in the SQL (stopping SQL injections.)  and comes out with
//htmlspecialchars in the view.
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

//This is for paging, counting the matching users and then working out how many pages
//there are (with at least 1).
$totalUsers = $dataSet->countUsers($search, $excludedId);
$totalPages = max(1, (int)ceil($totalUsers / $perPage));

//The current page from the URL. Only a number of 1 or more is accepted otherwise it will
//always remain as page 1. min() stops it going past the last page.
$page = 1;

if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] >= 1) {
    $page = (int)$_GET['page'];
}

$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage; //this shows how many users to skip, e.g. page 3 skips 20 users if 10 are
//displayed on each page.

//Fetches the users for this specific page.
$view->users = $dataSet->fetchUserPaging($perPage, $offset, $search, $excludedId);

//Friend count for each user on this page, stored by userId so the view can look it up.
$view->friendCount = [];
foreach ($view->users as $friend) {
    $view->friendCount[$friend->getId()] = $friendSet->friendCount($friend->getId());
}

//If the user is logged in, the friendship/ pending request between them and each user will be
//fetched here. The view uses it to show the correct status of Request friend/Request pending/
//Friends!
$view->friendships = [];
if ($view->loggedIn) {
    foreach ($view->users as $friend) {
        $view->friendships[$friend->getId()] = $friendSet->fetchFriendships($view->currentUserId, $friend->getId());
    }
}

//A one time message, for example the "request sent!" which is saved in the session by a
//different controller. It's shown once before being unset, so it doesn't appear again when the page
//is refreshed.
if (isset($_SESSION["message"])) {
    $view->message = $_SESSION["message"];
    unset($_SESSION["message"]);

}

//Paging information for the view's page links.
$view->totalUsers = $totalUsers;
$view->page = $page;
$view->totalPages = $totalPages;
$view->search = $search;

require_once('Views/users.phtml');


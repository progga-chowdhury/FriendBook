<?php

session_start();
require_once('Models/UserDataSet.php');
require_once('Models/User.php');

//Controller for the edit profile page: lets the logged-in user change their
//username, email and profile picture. It shows the "GET" form and also handles the
//form being submitted by "POST".
$userAccount = new User();

//Only logged-in users can be here, otherwise they are sent to the login page, "exit" stops
//the script so that nothing below it runs.
if (!$userAccount->userLoggedIn()) {
    header('Location: login.php');
    exit;
}

//The id comes from the session and never from the form, so a user can only
//ever edit their own profile and no one else's. The type casting (int) makes sure that
//it is also a whole number.
$me = (int)$_SESSION['userId'];
$userDataSet = new UserDataSet();
$user = $userDataSet->fetchUserById($me);

//These fill the view file with the user's current details so that the form starts
//pre-filled.
$view = new stdClass();
$view->pageTitle = 'Edit Profile';
$view->errors = [];
$view->saved = isset($_GET['saved']); //true after a successful save to show the actual "saved profile" message.
$view->username = $user->getUsername();
$view->email = $user->getEmail();
$view->photoPath = $user->getPhotoPath();

//Runs only when the save button is pressed
if (isset($_POST['save'])) {
    //reads the form values, defaulting to empty so that there are no "undefined-key" errors.
    $username = '';
    if (isset($_POST['username'])) {
        $username = $_POST['username'];
    }

    $email = '';
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
    }
    //Puts what the user typed back into the view, so the form keeps their input if there's an error.
    $view->username = $username;
    $view->email = $email;

    //Checks the username is between 3-30 letters, numbers or underscores and not already taken
    //by someone else. The user's own id is passed so keeping their current name isn't flagged as
    //an error.
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $view->errors['username'] = 'Username must be 3-30 letters, numbers or underscores';
    } elseif ($userDataSet->userNameAlreadyExists($username, $me)) {
        $view->errors['username'] = 'Username already taken';
    }

    //checks the email is a valid format and not too long, with the length limited to 100.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $view->errors[] = 'Email must be a valid email address';
    }


    //checks and saves the picture, but only if one was chosen by the user.
    //keeps the current picture by default if no new picture is chosen.
    $photoName = $user->getPhoto();
    $hasUpload = isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE;

    //only handles the picture if the other details are valid so that nothing is saved on a failed form.
    if ($hasUpload && count($view->errors) === 0) {
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $view->errors[] = 'The picture could not be uploaded';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $view->errors[] = 'The picture must be smaller than 2 MB.';
        } else {
            //saveNewImage checks that the file is actually a JPEG or PNG and returns the saved name,
            //or it returns an empty string if rejected.
            $savedImgName = $userAccount->saveNewImage($_FILES['photo']['tmp_name'], $me);
            if ($savedImgName == "") {
                $view->errors[] = 'The picture could not be uploaded';
            } else {
                $photoName = $savedImgName;
            }
        }
    }

    //If there are no errors, the save goes to the database and then redirects back to this page
    //so that refreshing the browser doesn't re-submit the form.
    if (count($view->errors) === 0) {
        $userDataSet->profileUpdate($me, $username, $email, $photoName);
        $_SESSION['username'] = $username; //so the menu shows the new name
        header('Location: edit_profile.php?saved=1');
        exit;

    }
}

require_once('Views/edit_profile.phtml');
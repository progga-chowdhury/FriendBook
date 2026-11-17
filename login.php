<?php

session_start();


require_once('Models/UserDataSet.php');
require_once('Models/Captcha.php');
require_once('Models/InputFilter.php');
require_once('Models/User.php');

//Controller for the login page, it shows the login form and handles it being submitted, all the
//checks run in a neat, fixed order and stop at the first one that fails. From the login lockout, to input
//format, to captcha and to username and password.
$captcha = new Captcha();
$inputFilter = new InputFilter();
$userLogin = new User();
$view = new stdClass();
$view->pageTitle = 'Log in';
$view->errorMessage = '';

//Stays empty unless the login is successful, then it holds the page
//to send the user to.
$current_destination = '';

//runs only when the login button is pressed.
if (isset($_POST["loginButton"])) {

    //reads the form values, defaulting to empty so that a missing field
    //doesn't cause an "undefined-key" error. The username is trimmed, with any tags
    //stripped and with a max of 20 characters. The password is left as it's typed as it will
    //be compared to the stored hash.
    $username = "";
    if (isset($_POST["username"])) {
        $username = $inputFilter->cleanTextInput($_POST["username"], 20);
    }

    $password = "";
    if (isset($_POST["password"])) {
        $password = $_POST["password"];
    }

    $captchaResult = "";
    if (isset($_POST["captchaResult"])) {
        $captchaResult = trim($_POST["captchaResult"]);
    }

    //If the user gets locked out from 3 failed attempts, the login page immediately
    //stops any further attempts and displays the countdown.
    if ($userLogin->loginLockedOut()) {
        $view->errorMessage = 'Too many failed attempts! Try again in ' . $userLogin->getTimeLeft() . ' seconds.';
    }

    //checking if the username and password are in a valid format in both length and characters. If not, display
    //specific error message.
    elseif (!$inputFilter->loginValidation($username, $password)) {
        $view->errorMessage = implode(" ", $inputFilter->getErrors());
    }

    //checks if the maths arithmetic captcha question is correct. If not, then
    //it displays an error message.
    elseif (!$captcha->getCaptchaAnswer($captchaResult)) {
        $view->errorMessage = "Invalid result, please try again.";
    }

    //checks if the username and password actually match a user. If yes, the next page
    //will become users.php where the user will be redirected to.
    elseif ($userLogin->authenticateLogin($username, $password)) {
        $current_destination = "users.php";
    }

    //Otherwise, if there are wrong details.
    // That failed attempt might have been the 3rd one which then
    //locks the login form, allowing the lockout message to be displayed to the user. If
    //it wasn't the 3rd attempt, then display the normal error.
    else {
        if ($userLogin->loginLockedOut()) {
            $view->errorMessage = 'Too many failed attempts! Try again in ' . $userLogin->getTimeLeft() . ' seconds.';
        } else {
            $view->errorMessage = "Invalid username or password.";
        }
    }

}

//If the login succeeds, redirect the user to the chosen page. "exit" stops the script
//from running any further.
if ($current_destination != "") {
    header('Location: ' . $current_destination);
    exit;
}

//Gets the captcha question to show on the form as a new sum
//is created after every attempt.
$view->captchaQuestion = $captcha->getCaptchaQuestion();

require_once('Views/login.phtml');

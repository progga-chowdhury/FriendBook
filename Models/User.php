<?php

require_once("Models/UserDataSet.php");

//This class handles the login, logout, the login lockout and profile picture saving. It
// uses UserDataSet to fetch the user from the database. For the lockout, the failed
//attempt counter and the lockout time are stored inside the session.
class User
{
    protected $_userDataSet;
    protected $_maxLoginAttempts; //max failed attempts allowed before lockout.
    protected $_loginTimeout; //lockout time in seconds.

    //The constructor sets the limits of 3 login attempts and 120 seconds of lockout time, it also
    //makes sure that the session counters exist so that other methods never read a missing session key.
    public function __construct()
    {
        $this->_userDataSet = new UserDataSet();
        $this->_maxLoginAttempts = 3;
        $this->_loginTimeout = 120;

        if (!isset($_SESSION['failedLoginAttempts'])) {
            $_SESSION['failedLoginAttempts'] = 0;
        }

        if (!isset($_SESSION['lockUntil'])) {
            $_SESSION['lockUntil'] = 0;
        }
    }

    //This method checks the login details. The user is fetched by their username, then
    //the password_verify method compares the typed password with the stored hash.
    //If successful, it saves the id and username in the session and resets the login fail counter to 0.
    //If unsuccessful, every login failure counts as a failed attempt. A wrong username and a wrong
    //password both contribute to the full login being incorrect, therefore the site doesn't specify which input is
    //incorrect as they both fall into the else statement.
    public function authenticateLogin($username, $password)
    {

        $loginSuccess = false;

        $userData = $this->_userDataSet->fetchUserByUsername($username);

        if ($userData && password_verify($password, $userData->getPassword())) {
            $_SESSION['userId'] = $userData->getId();
            $_SESSION['username'] = $userData->getUsername();
            $this->resetLoginFails();
            $loginSuccess = true;
        } else {
            $this->countLoginFails();
        }
        return $loginSuccess;
    }

    //True if a user is logged in, which is decided by whether a userId exists in the session.
    //The controllers call this method and the views only display what the controller then decides.
    public function userLoggedIn()
    {
        $userLoggedIn = false;

        if (isset($_SESSION['userId'])) {
            $userLoggedIn = true;
        }
        return $userLoggedIn;
    }

    //Logs the user out and empties the session data, then destroys the session itself.
    public function userLoggedOut()
    {
        $_SESSION = [];
        session_destroy();
    }

    //Remains true while the login lockout is still active.
    public function loginLockedOut()
    {
        $loginLocked = false;

        if (($_SESSION['lockUntil'] > time())) {
            $loginLocked = true;
        }
        return $loginLocked;
    }

    //This method fetches the time remaining for the lockout, it never goes below 0,
    //the login page uses it to show the countdown message until the user is able to attempt
    //another login.
    public function getTimeLeft()
    {
        $timeLeft = $_SESSION['lockUntil'] - time();

        if ($timeLeft < 0) {
            $timeLeft = 0;
        }
        return $timeLeft;
    }

    //This method adds each failed attempt, when it reaches the maximum of 3 it sets the
    //lockout time and resets the fail counter. It's a protected method as only the
    //authenticateLogin() method in this class should call it.
    protected function countLoginFails()
    {
        $_SESSION['failedLoginAttempts'] = $_SESSION['failedLoginAttempts'] + 1;

        if ($_SESSION['failedLoginAttempts'] >= $this->_maxLoginAttempts) {
            $_SESSION['lockUntil'] = time() + $this->_loginTimeout;
            $_SESSION['failedLoginAttempts'] = 0;
        }
    }

    //This method clears the fail counter and any lockout after a successful login.
    protected function resetLoginFails()
    {
        $_SESSION['failedLoginAttempts'] = 0;
        $_SESSION['lockUntil'] = 0;
    }

    //This method saves an uploaded profile picture and returns the new file name, or it returns
    //an empty string if the file isn't allowed. The getimagesize() method reads the real file contents, eg. renaming a file
    //from .txt to .jpg will still return false as only JPEG and PNG are accepted.
    //The file name is then created by the server, with the userid followed by image
    //extension of either .jpg or .png, the user isn't able to choose a path or overwrite
    //other files, and each user only has one picture.
    public function saveNewImage($imgFile, $userId)
    {

        $imgInfo = getimagesize($imgFile);
        if ($imgInfo === false) {
            return "";
        }

        if ($imgInfo[2] == IMAGETYPE_JPEG) {
            $imgExtension = ".jpg";
        } else if ($imgInfo[2] == IMAGETYPE_PNG) {
            $imgExtension = ".png";
        } else {
            return "";
        }

        $imgName = $userId . $imgExtension;
        move_uploaded_file($imgFile, __DIR__ . "/../profiles/" . $imgName);
        return $imgName;
    }

}
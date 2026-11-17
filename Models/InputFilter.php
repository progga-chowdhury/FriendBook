<?php

//This class checks and cleans any user login input before it's used.
//Any failed checks add an error message to the _errors array which then the
//controller displays to the user. The length limits are set in the constructor so they only
//need changing in one place.
class InputFilter
{
    protected $_usernameMin;
    protected $_usernameMax;
    protected $_passwordMin;
    protected $_passwordMax;
    protected $_errors;

    //The constructor sets the minimum and maximum length limit for usernames,
    //the error list is started empty.
    public function __construct()
    {
        $this->_usernameMin = 3;
        $this->_usernameMax = 20;
        $this->_passwordMin = 4;
        $this->_passwordMax = 100;
        $this->_errors = [];
    }

    //Adds an error message into the error list, this method is protected because only this class's
    //own checks should add errors, nothing else.
    protected function addErrors($message)
    {
        $this->_errors[] = $message;
    }

    //Returns all the error messages so that the controller can pass them into the
    //view to display.
    public function getErrors()
    {
        return $this->_errors;
    }

    //Cleans an inputted text value. "trim" removes any space at the start/end of the text,
    //"strip_tags" removes any HTML tags as a defense against XSS, and "substr" cuts the text
    //to the maximum length. The output comes through htmlspecialchars in the views as a second
    //layer of protection.
    public function cleanTextInput($inputValue, $maxLength)
    {

        $inputValue = trim($inputValue);
        $inputValue = strip_tags($inputValue);
        $inputValue = substr($inputValue, 0, $maxLength);
        return $inputValue;
    }

    //This method checks that both the username and the password are correct in format. Both checks always run
    //so that the user sees every error at once. Returns true only if both inputs
    //are valid.
    public function loginValidation($username, $password)
    {

        $isValid = true;

        if (!$this->usernameValidation($username)) {
            $isValid = false;
        }

        if (!$this->passwordValidation($password)) {
            $isValid = false;
        }

        return $isValid;
    }

    //Usernames must all be between 3-20 characters + only contains letters,
    //numbers, underscores and hyphens. /[^A-Za-z0-9_-]/ matches for any character that
    //is not in the allowed list. In the case that it finds one, the username becomes invalid.
    public function usernameValidation($username)
    {
        $isValid = true;

        if (strlen($username) < $this->_usernameMin || strlen($username) > $this->_usernameMax) {
            $this->addErrors("Your username must be between " . $this->_usernameMin . " and " . $this->_usernameMax . " characters. ");
            $isValid = false;
        }

        if (preg_match('/[^A-Za-z0-9_-]/', $username)) {
            $this->addErrors("Your username must only contain letters,numbers, underscores and hyphens.");
            $isValid = false;
        }

        return $isValid;
    }

    //This method checks that the password must be between 4-100 characters. There are no
    //character rules because it gets hashed and is never displayed. Therefore, any special
    //characters are safe to use.
    public function passwordValidation($password)
    {
        $isValid = true;

        if (strlen($password) < $this->_passwordMin || strlen($password) > $this->_passwordMax) {
            $this->addErrors("Your password must be between " . $this->_passwordMin . " and " . $this->_passwordMax . " characters.");
            $isValid = false;
        }
        return $isValid;
    }


}
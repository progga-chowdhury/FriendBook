<?php

//Simple arithmetic math captcha, stopping bots from continuously spamming
//The answer is kept in the session and never sent over to the browser, this way
//the user can't read or edit it.
class Captcha
{
//Makes a new sum of two randomly generated numbers between 0-9 and stores question + answers
//in the session together.
    public function generateCaptcha()
    {

        $num1 = rand(0, 9);
        $num2 = rand(0, 9);

        $_SESSION['captchaResult'] = $num1 + $num2;
        $_SESSION['captchaQuestion'] = "What is " . $num1 . " + " . $num2 . "?";
    }

//Returns the captcha question for the login view file to show. Only creates a
//new one if there's no existing question yet, so refreshing the page still keeps the
//question the same.
    public function getCaptchaQuestion()
    {
        if (!isset($_SESSION['captchaQuestion'])) {
            $this->generateCaptcha();
        }
        return $_SESSION['captchaQuestion'];

    }

//Checks the user's inputted answers. Returning true if correct and false if incorrect/otherwise.
    public function getCaptchaAnswer($userAnswer)
    {
        $correct_answer = false;

        //"is_numeric" rejects any inputted text, the type casting (int) is used on both sides so
        //that eg. a string of "7" compares equally to an integer of 7.
        if (isset($_SESSION['captchaResult']) && is_numeric($userAnswer)) {
            if ((int)$userAnswer === (int)$_SESSION['captchaResult']) {
                $correct_answer = true;
            }

        }

        //Always generates a new captcha question after an attempt so that a correct answer
        //cannot be reused.
        $this->generateCaptcha();
        return $correct_answer;
    }


}
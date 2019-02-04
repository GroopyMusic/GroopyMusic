<?php

namespace AppBundle\Services;

class CaptchaManager
{
    private $secret;

    public function __construct($google_captcha_api_secret)
    {
        $this->secret = $google_captcha_api_secret;
    }

    public function verify() {
        if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response']))
        {
            $secret = $this->secret;
            $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response']);
            $responseData = json_decode($verifyResponse);
            if($responseData->success) {
                return true;
            }
            else {
                var_dump($responseData);
                return false;
            }
        }
        return null;
    }
}
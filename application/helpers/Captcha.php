<?php

class Captcha {

    public function __construct()
    {
        require_once('/includes/vendor/google/recaptcha/src/autoload.php');
    }

    public function checkCaptcha($field)
    {
        global $settings;
        try {
            $recaptcha = new \ReCaptcha\ReCaptcha($settings['RECAPTCHA_KEY']);
            $resp = $recaptcha->verify($field, $_SERVER['REMOTE_ADDR']);
            return $resp->isSuccess();
        } catch (Exception $e){
            error_log($e);
            return false;
        }    
    }

}

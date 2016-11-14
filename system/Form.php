<?php
class Form
{
    private static $instance = null;
    private $safe = array();
    private $fieldmessage = array();
    private $token = null;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->safe['get'] = new stdClass();
        $this->safe['post'] = new stdClass();

        $this->doSafeGet();
        $this->doSafePost();
    }

    private function doSafePost()
    {
        foreach($_POST as $key=>$value) {
            $this->safe['post']->$key = Controller::sanitize($value);
        }
    }

    private function doSafeGet()
    {
        foreach($_GET as $key=>$value) {
            $this->safe['get']->$key = Controller::sanitize($value);
        }
    }

    public function getGet($key = false)
    {
        if (!$key) {
            $return = $this->safe['get'];
        } else if (is_array($key)) {
            $return = new stdClass();
            foreach ($key as $value) {
                $return->$value = $this->getGet($value);
            }
        } else if (isset($this->safe['get']->$key)) {
            $return = $this->safe['get']->$key;
        } else {
            $return = false;
        }

        return $return;
    }

    public function getPost($key = false)
    {
        if (!$key) {
            $return = $this->safe['post'];
        } else if (is_array($key)) {
            $return = new stdClass();
            foreach ($key as $value) {
                $return->$value = $this->getPost($value);
            }
        } else if (isset($this->safe['post']->$key)) {
            $return = $this->safe['post']->$key;
        } else {
            $return = false;
        }

        return $return;
    }

    public function isFieldMSGset()
    {
        return !(empty($this->fieldmessage));
    }

    public function getFieldMessage($key)
    {
        if (isset($this->fieldmessage[$key]['message'])) {
            $message = $this->fieldmessage[$key]['message'];
        } else {
            $message = false;
        }

        return $message;
    }

    public function getFieldStatus($key)
    {
        if (isset($this->fieldmessage[$key]['status'])) {
            $status = 'has-' . $this->fieldmessage[$key]['status'];
        } else {
            $status = false;
        }

        return $status;
    }

    public function isFormValid()
    {
        $prevToken = isset($this->safe['post']['csrf'])
                    ? $this->safe['post']['csrf']
                    : $this->safe['get']['csrf'];

        if (! $prevToken || $prevToken !== $this->csrfToken){
            error_log('CSRF violation on ' . $_SERVER['REQUEST_URI']);
            return false;
        }
        
        foreach ($this->fieldmessage as $field) {
            if ((array_key_exists('status', $field)) && ($field['status'] != 'success')) {
                return false;
            }
        }

        return true;
    }

    public function validateDate($datekey, $daykey, $monthkey, $yearkey, $method = 'post')
    {
        if ((isset($this->safe[$method]->$daykey)) && (isset($this->safe[$method]->$monthkey)) && (isset($this->safe[$method]->$yearkey)) && (checkdate($this->safe[$method]->$monthkey, $this->safe[$method]->$daykey, $this->safe[$method]->$yearkey))) {
            $this->fieldmessage[$datekey]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$datekey]['message'] = 'must be a valid date' . $datekey . $daykey . $monthkey . $yearkey ;
            $this->fieldmessage[$datekey]['status'] = 'error';
            $return = false;;
        }

        return $return;
    }

    public function validateLength($key, $minlength = 1, $maxlength = false, $method = 'post')
    {
        if ((isset($this->safe[$method]->$key)) && (strlen($this->safe[$method]->$key) >= $minlength) && ($maxlength == false or strlen($this->safe[$method]->$key) <= $maxlength)) {
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "Must be at least $minlength characters long.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }

        return $return;
    }

    public function validateNumeric($key, $method = 'post')
    {
        if ((isset($this->safe[$method]->$key)) && (is_numeric($this->safe[$method]->$key))) {
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "Must be numeric.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }

        return $return;
    }

    public function validateInteger($key, $method = 'post')
    {
        if ((isset($this->safe[$method]->$key)) && (is_int((int)$this->safe[$method]->$key))) {
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "Must be an integer.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }

        return $return;
    }

    public function validateInterval($key, $max = 50, $min = 0, $method = 'post')
    {
        if ((isset($this->safe[$method]->$key)) && ($this->safe[$method]->$key >= $min) && ($this->safe[$method]->$key <= $max)) {
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "Must be between $min and $max.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }

        return $return;
    }

    public function validateEmail($key, $method = 'post')
    {
        if ((isset($this->safe[$method]->$key)) && (filter_var($this->safe[$method]->$key, FILTER_VALIDATE_EMAIL))) {
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "This is not a valid email address.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }

        return $return;
    }

    public function validateTelephone($key, $method = 'post')
    {
        $return = false;
        if (isset($this->safe[$method]->$key)){
            $num = $this->safe[$method]->$key;
            $num = preg_replace("/^\+/", '',$num);
            $num = preg_replace("/^0/", '', $num);
            $num = preg_replace("/-/", '', $num);

            if (strlen($num) == 11){
                $this->safe[$method]->$key = $num;
                $return = true;
            }
        }
        if ($return == true){
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "This is not a valid phone number. Don't forget the country code.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }
    }

    public function validateEqual($key1, $key2, $method = 'post')
    {
        $return = false;
        if (isset($this->safe[$method]->$key1) and isset($this->safe[$method]->$key2)){
            $return = ($this->safe[$method]->$key1 == $this->safe[$method]->$key2);
        }
        if ($return == true){
            $this->fieldmessage[$key1]['status'] = 'success';
            $this->fieldmessage[$key2]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key1]['message'] = "Does not match.";
            $this->fieldmessage[$key1]['status'] = 'error';
            $this->fieldmessage[$key2]['message'] = "Does not match.";
            $this->fieldmessage[$key2]['status'] = 'error';
            $return = false;
        }
        return $return;
    }

    public function validateUsername($key, $method = 'post')
    {
        if ((isset($this->safe[$method]->$key)) && (ctype_alpha($this->safe[$method]->$key)) 
            && (strlen($this->safe[$method]->$key) >= 4)
            && (strlen($this->safe[$method]->$key) <= 20)) {
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "Your username can only contain letters and must be between 4 and 20 chars long.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }

        return $return;

    }

    public function validateComplexity($key, $minLength=12, $maxLength=64, $method='post')
    {
        $return = false;
        if (isset($this->safe[$method]->$key)){
            $return = preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", 
                                 $this->safe[$method]->$key)
		      && (strlen($this->safe[$method]->$key) >= $minLength)
		      && (strlen($this->safe[$method]->$key) <= $maxLength);
        }
        if ($return == true){
            $this->fieldmessage[$key]['status'] = 'success';
            $return = true;
        } else {
            $this->fieldmessage[$key]['message'] = "Does not include 1 lowercase and 1 uppercase letter and 1 symbol.";
            $this->fieldmessage[$key]['status'] = 'error';
            $return = false;
        }
        return $return;
  
    }
}

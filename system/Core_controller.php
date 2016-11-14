<?php

abstract class Core_controller
{
    protected $template;
    protected $data = array();

    public function __construct($protected=false, $layout='default')
    {
        // load settings
        global $settings;
        $this->settings = $settings;

        // setup Form instance for handling GET/POST
        $this->form = Form::getInstance();
        $this->form->csrfToken = $_SESSION['csrfToken'];

        // setup template + flashmessage + CSRF token
        $this->template = new Template($layout);
        $this->template->flashmessage = $this->getFlashMessage();
        $this->template->csrfToken = $_SESSION['csrfToken'] = generateCSRFToken();

        // language
        $this->setupLanguage();

        // check access
        if ($protected) $this->checkAccess();

        // menu
        $this->setMenu($protected);
    }

    private function setupLanguage()
    {
        // check lang
        if (! isset($_SESSION['lang']))
            $_SESSION['lang'] = $this->settings['DEFAULT_LANG'];

        // load lang
        $this->langs_m = isset($this->langs_m) ? $this->langs_m : Load::helper('langs_m');
        $this->langs   = $this->template->langs = $this->langs_m->getLangs();
        $this->langStr = $this->template->langStr = $this->langs_m->getStrings($_SESSION['lang']);
        $this->currentLang = $this->template->currentLang = $_SESSION['lang'];

    }

    public function setMenu($isProtectedPage)
    {
        $this->menu_m = isset($this->menu_m)
                            ? $this->menu_m
                            : Load::model('menu_m');
        $this->template->menuitems = $this->menu_m->getMenu($this->getUser() != false
                                        ? $this->getUser()->usr_role
                                        : false, $isProtectedPage, $this->langs);
    }

    function encrypt($key, $plaintext)
    {
        $iv = openssl_random_pseudo_bytes(16);
        return base64_encode($iv . openssl_encrypt($plaintext, 'aes-128-cbc', 
                                                   $key, OPENSSL_RAW_DATA, $iv));
    }

    function decrypt($key, $enctext)
    {
        $decoded = base64_decode($enctext);
        $iv = substr($decoded, 0, 16);
        $enc = substr($decoded, 16);
        return openssl_decrypt($enc, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user']);
    }

    public function getUser()
    {
        if (! $this->user && $this->isLoggedIn()){
            $this->user_m = isset($this->user_m) ? $this->user_m : Load::model('user_m');
            $this->user = $this->template->user = $this->user_m->getUserById($_SESSION['user']);

            // if user not found in db, destroy user session        
            if (! $this->user){
                session_destroy();
                return $this->redirect('user/logout');
            }
        }
        return $this->user;
    }

    public function getUserToken()
    {
	    return hash('sha256', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    public function generateCSRFToken()
    {
        return bin2hex(random_bytes(32));
    }

    public function checkAccess()
    {
        // is logged in
        if (! $this->getUser()){
            $this->setFlashmessage($this->lang['accessdenied'], 'danger');
            return $this->redirect('home/index');
        }
        
        // check token
        if ($_SESSION['usertoken'] !== $this->getUserToken()) {
            session_destroy();
            $this->setFlashmessage($this->lang['tokenviolation'], 'danger');
            return $this->redirect('user/login');
        }

        return true;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return false;
    }

    public function setFlashmessage($message, $status = 'success')
    {
        $flashmessage = new stdClass();
        $flashmessage->status = $status;
        $flashmessage->message = $message;

        $_SESSION['flashmessage'] = $flashmessage;

        return $this;
    }

    public function setCurrentFlashmessage($message, $status = 'success')
    {
        $this->template->flashmessage = new stdClass();
        $this->template->flashmessage->status = $status;
        $this->template->flashmessage->message = $message;

        return $this;
    }

    public function getFlashMessage()
    {
        if (isset($_SESSION['flashmessage'])) {
            $flashmessage = $_SESSION['flashmessage'];
            unset($_SESSION['flashmessage']);
        } else {
            $flashmessage = false;
        }
        return $flashmessage;
    }

    public function redirect($newlocation = '')
    {
	    global $settings;
        if (!headers_sent($filename, $linenum)) {
            header('Location: ' . URL::base_uri($newlocation));
            exit();
        } else {
            echo "Headers already sent in $filename on line $linenum\n";
        }
    }
}

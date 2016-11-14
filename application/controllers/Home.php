<?php

class Home extends Core_controller
{
    public function __construct()
    {
        parent::__construct(false);

        $this->template
            ->setPartial('headermeta')
            ->setPartial('navbar')
            ->setPartial('flashmessage')
            ->setPartial('footer');

        $this->template->setPagetitle('Ducky');
    }

    public function index()
    {
        # show frontpage
        return $this->template->render('home/index');
    }

    public function lang()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return $this->redirect('home/index');

        $formdata = $this->form->getPost();

        # needs language parameter
        if (! isset($formdata->language))
            return $this->redirect('home/index');

        # check if language exists
        if (! $this->langs_m->langExists($formdata->language)) {
            $this->setCurrentFlashmessage($this->lang['wronglang'], 'danger');
            return $this->template->render('home/index');
        }

        # set new language
        $_SESSION['lang'] = $formdata->language;
        return $this->redirect('home/index');
    }


    public function logout()
    {
        if ($this->isLoggedIn()){
            # destroy dession
            session_destroy();
        
            # logout user
            $this->setFlashmessage($this->lang['loggedout']);
        }

        return $this->redirect('home/index');
    }

    public function login()
    {
        # redirect if user is already logged in
        if ($this->isLoggedIn())
            return $this->redirect('dashboard/index');

        # set page title
        $this->template->setPagetitle('Login - ToastView');

        # render page if not processing login
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return $this->template->render('home/login');

        # validate login requirements
        $formdata = $this->form->getPost();
        $this->form->validateLength('username', 4 , 20);
        $this->form->validateLength('password', 12, 100);

        # render page if requirements not met
        if (! $this->form->isFormValid()) {
            $this->template->formdata = $formdata;
            $this->setCurrentFlashmessage('You supplied an invalid username or password.', 'error');
            return $this->template->render('home/login');
        }

        # Load user if exists
        $this->user_m = isset($this->user_m) ? $this->user_m : Load::model('user_m');
        $user = $this->user_m->getUserByLogin($formdata->username);

        if ($user) {
            # login attempt needs captcha?
            $show_cap = false != $user->usr_pw_req_date
                        && $user->usr_pw_req_num >= 3
                        && time() <= strtotime('+ 10 minutes', strtotime($user->usr_pw_req_date));

            # show captcha
            if ($show_cap){
                $cap_input = isset($_POST['g-recaptcha-response'])
                             ? $_POST['g-recaptcha-response'] : false;
                
                $this->template->captcha = true;
                $this->template->formdata = $formdata;

                # captcha not valid
                if (! $cap_input or ! Load::helper('Captcha')->checkCaptcha($cap_input)){
                    $this->setCurrentFlashmessage('Please correct your captcha.', 'error');
                    return $this->template->render('index/login');
                }
            }
        }

        # compare password
        global $settings;
        $success = password_verify(hash('sha256', $formdata->password), $user ? $user->usr_pass : 'foo');
        
        # show error when password invalid
        if (! $user || ! $success){
            if ($user) $this->user_m->addLoginAttempt($user->usr_id);
            $this->template->formdata = $formdata;
            $this->setCurrentFlashmessage('You supplied an invalid username or password.', 'error');
            return $this->template->render('index/login');
        }

        # login success
        $this->user_m->resetLoginAttempts($user->usr_id);
        $_SESSION['user'] = $user->usr_id;
        $_SESSION['usertoken'] = $this->getUserToken();
        session_regenerate_id(true);

        return $this->redirect('dashboard/index');
    }

}

<?php
/* Users controller */
class Users extends Controller
{
    public function index()
    {
        $this->render('user/index', array(
            'message' => 'Test response.'
        ));
    }

    public function login()
    {
        $message = 'Login successful!';
        $post = (object)$this->app->request()->post();
        $code = 200;
        $sess_token = null;
        $sess_expires = 0;

        $login_model = $this->loadModel('Login');
        // perform the login method, put result (true or false) into $login_successful
        
        if(!empty($username = $post->username) && !empty($password = $post->password)) {
            // call to model verify user & password
            // if user credentials ok
            // TODO: check if token already set and if it matches ... if not log out first
            $login = $login_model->login();
            if($login !== false) {
                if($login !== true) {
                    $sess_token = $login;
                    $sess_expires = 30;
                }
            } else {
                $message = 'Sorry! Wrong credentials.';
                $code = 401;
            }
        } else {
            // return message if missing credentials
            $message = 'Username or password missing.';
            $code = 401;
        }

        $this->render('json',array(
            'session_token' => $sess_token,
            'session_expires' => $sess_expires,
            'message' => $message
        ), $code);
    }

    public function addUser() {
        $login_model = $this->loadModel('Login');
        $registration_successful = $login_model->registerNewUser();

        if ($registration_successful == true) {
            $this->app->redirect(URL . 'login');
        } else {
            $this->app->redirect(URL . 'register');
        }
    }

    public function verify_login($user_id, $user_activation_verification_code)
    {
        if (isset($user_id) && isset($user_activation_verification_code)) {
            $login_model = $this->loadModel('Login');
            $login_model->verifyNewUser($user_id, $user_activation_verification_code);
            $this->render('user/verify');
        } else {
            $this->app->redirect(URL . 'login');
        }
    }

    public function logout()
    {
        $login_model = $this->loadModel('Login');
        $logout = $login_model->logout();

        $message = ($logout ? 'Logout successful!' : 'You were already logged out.');
        $this->render('json',array(
            'message' => $message
        ));
    }
}
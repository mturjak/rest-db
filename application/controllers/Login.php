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
        
        $sess_token = null;
        $sess_expires = 0;

        if(!empty($username = $post->username) && !empty($password = $post->password)) {
            // call to model verify user & password
            // if user credentials ok
            $x = false;
            if($password == "test" && $username == "martin") {

            } else {
                $this->render('error', array(
                    'message' => 'Sorry! Wrong credentials.'
                ), 403);
            }
        } else {
            // return message if missing credentials
            $message = 'Empty login credentials.';
        }
        
        // render JSON response if in API else redirect
        if($this->response_type == 'api'){
            $this->render('json',array(
                'session_token' => $sess_token,
                'session_expires' => $sess_expires,
                'message' => $message
            ));
        } else {
            $this->app->flashNow('info', 'Your credit card is expired');
            $this->render('index/index');
        }
    }

    /**************  Non-API speciffic methods *************/

    public function loginPage()
    {
        $arg = (!isset($name) ? '' : " Action: {$name}");
        $this->render('user/loginpage', array(
            'message' => "Test login response.{$arg}"
        ));
    }

    public function logout()
    {
        \Middleware\Session::destroy();
        $this->render('index/index', array(
            'message' => "Logout successful. Session terminated."
        ));
    }
}
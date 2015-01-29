<?php

namespace Middleware;

/**
 * Class Auth
 * Simply checks if user is logged in. In the app, several controllers use Auth::handleLogin() to
 * check if user if user is logged in, useful to show controllers/methods only to logged-in users.
 */
class Auth extends \Slim\Middleware
{


    public function call()
    {
        $app = $this->app;
        $req = $app->request();
        $resp = $app->response();
        $this->realm = "API";

        // TODO: move to configurations:
        $this->ref_app_id = 'APPLICATION_ID_STRING';
        $this->ref_api_key = 'REST_API_KEY';

        if(empty($app_id = $req->headers('X-Application-Id'))) {
            $app_id = $req->headers('PHP_AUTH_USER');
        }
        if(empty($api_key = $req->headers('X-REST-API-Key'))) {
            $api_key = $req->headers('PHP_AUTH_PW');
        }

        if(empty($app_id) || empty($api_key) || $app_id !== $this->ref_app_id || $api_key !== $this->ref_api_key ) {
            $resp->setStatus(401);
            $resp->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
            $app->response->setBody(json_encode(array(
                'error' => true,
                'message' => 'Authentication error! You need to provide correct Application-Id and REST-API-Key.'
            )));
        }
        $this->next->call();
    }

    public static function authBase() {
        return function () {
            $user = true;
            if ( $user === false ) {
                $app = \Slim\Slim::getInstance();
                $app->flash('error', 'Login required');
            }
        };
    }

    public static function authMember() {
        return function () {
            $user = true;
            if ( $user === false ) {
                $app = \Slim\Slim::getInstance();
                $app->flash('error', 'Login required');
            }
        };
    }

    public static function authSession() {
        $app = \Slim\Slim::getInstance();
        $req = $app->request();
        $resp = $app->response();

        if(!empty($token = $req->headers('X-Session-Token'))) {
            $model = new \UserModel();
            $checkToken = $model->checkToken($token);
            if($checkToken !== false) {
                if($checkToken !== true) {
                    $resp->headers->set('X-New-Session-Token', $checkToken);
                }
                // call next
            } else {
                $resp->setStatus(401);
                $resp->setBody(json_encode(array(
                    'error' => true,
                    'message' => 'Your token is invalid! Log in to get a new one.'
                )));
            }
        } else {
            $resp->setStatus(401);
            $resp->setBody(json_encode(array(
                'error' => true,
                'message' => 'Session token required! Please log in to get one.'
            )));
        }
    }

    public static function verifyCode() {
        return function () {
            $user = true;
            if ( $user === false ) {
                $app = \Slim\Slim::getInstance();
                $app->flash('error', 'Login required');
            }
        };
    }

    /**
     * check if user is logged in ... if not destroy session
     */
    public static function handleLogin()
    {
        // initialize the session
        \Session::init();
        // if user is still not logged in, then destroy session, handle user as "not logged in" and
        // redirect user to login page
        if (!isset($_SESSION['user_logged_in'])) {
            \Session::destroy();
            header('location: ' . URL . 'login');
            // to prevent fetching views via cURL (which "ignores" the header-redirect above) we leave the application
            // the hard way, via exit(). @see https://github.com/panique/php-login/issues/453
            exit();
        }
    }

    /**
     * check if user is logged in ... if not destroy session
     */
    public static function userLoggedIn()
    {
        $app = \Slim\Slim::getInstance();
        // initialize the session
        \Session::init();

        if (!isset($_SESSION['user_logged_in'])) {
            if (isset($_COOKIE['rememberme'])) {
                $app->redirect(URL . 'loginWithCookie');
            } else {
                return false;
            }
        }
        return true;
    }
}

<?php
/* Router class */
class Router
{

    private $app;

    public function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
        $this->setRoutes();
    }

    private function setRoutes()
    {
        $app = $this->app;
        require 'application/config/routes.php';
    }

    /**
     * Load controller and call controller method
     */
    private function loadController($controller = null, $action = null, $param1 = null, $param2 = null)
    {
        if(isset($controller)) {
            $controller = ucfirst($controller);
            $controller = new $controller();
            if (isset($action)) {
                if (method_exists($controller, $action)) {
                    if (isset($param2)) {
                        $controller->{$action}($param1, $param2);
                    } elseif (isset($param1)) {
                        $controller->{$action}($param1);
                    } else {
                        // if no parameters given, just call the method without arguments
                        $controller->{$action}();
                    }
                } else {
                    // TODO: go to error page
                }
            } else {
                $controller->index();
            }
        } else {
            $controller = new Index();
            $controller->index();
        }
    }
}
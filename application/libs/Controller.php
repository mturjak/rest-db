<?php

/**
 * This is the "base controller class". All other "real" controllers extend this class.
 * Whenever a controller is created, we also
 * NOT FOR API 1. initialize a session
 * NOT FOR API 2. check if the user is not logged in anymore (session timeout) but has a cookie
 * 3. create a database connection (that will be passed to all models that need a database connection)
 * 4. render a view
 */
class Controller
{
    /**
     * @var Slim instance
     */
    protected $app;

    /**
     * Base controller constructor 
     * Gets instance of Slim and establishes db connection
     */
    public function __construct()
    {
        $this->app = Slim\Slim::getInstance();

        // create database connection
        try {
            $this->db = Database::getInstance();
        } catch (PDOException $e) {
            $this->halt(503, 'Database connection could not be established.');
        }
    }

    /**
     * Loads the model with the given name.
     * @param $name string name of the model
     */
    public function loadModel($name)
    {
        $modelName = $name . 'Model';
        return new $modelName($this->db);
    }

    /**
     * Returns a simple response (status code, message), without any data output
     * @param int $status_code HTTP response code
     * @param string $message Optional, response message
     * @param bool $is_error Optional, overrides response error switch
     */
    protected function halt($status_code, $message = '', $is_error = null)
    {
        // set default template
        $template = 'index/index';

        // set error switch based on the $status_code or use $is_error override
        $response["error"] = (isset($is_error) ? $is_error : $status_code >= 400);

        // combine default (status_sode based) message our custom message
        $response["message"] = Slim\Http\Response::getMessageForCode($status_code)
                               . ( $message !== '' ? ': ' . $message : '');

        $this->render($template, $response, $status_code);

        // stop execution
        $this->app->stop();
    }

    /**
     * Renders the response (status code, message and data) with the selected template
     * @param string $template
     * @param array $response Any parameter-value pairs you wan't to pass along to the view
     * @param int $status_code HTTP response code
     * @param bool $render_without_header_and_footer Turns header/footer on/off (gets overridden if `responseType=api`)
     */
    protected function render($template, $response = array(), $status_code = 200, $render_without_header_and_footer = false)
    {
        if(DEBUG_MODE){
            $response = array_merge($response, array(
                'request' => $this->app->req_str,
                'response_code' => $status_code
            ));
        }

        // if response type is 'api' return response in json format
        $this->app->response->status($status_code);

        // pull together all echoed content
        $res_body = $this->app->response->finalize();
        if(empty($res_body[2])){
            echo(json_encode($response));
        }
        $this->app->stop();
    }

    /**
     * Gets active controller and action from `debug_backtrace` and stores them in `view->data->mvc`
     */
    protected function activeControllerAction() {
        $backtrace = debug_backtrace();
        $controller = $backtrace[2];
        $mvc = (object)array(
            'controller' => strtolower($controller['class']),
            'action' => strtolower($controller['function']),
            'args' => $controller['args']
        );
        $this->app->view->set('mvc', $mvc);
    }
}

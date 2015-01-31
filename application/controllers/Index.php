<?php

/**
 * Class Index
 * The index controller
 */
class Index extends Controller
{
    /**
    * Constructor needs to be explicitely defined so that method index() doesn't get used as constructor
    */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Default controller-action when user gives no input.
     */
    public function index()
    {
        $app = $this->app;

        // some generic server response maybe indicating api status - TODO: load from config
        $genericApiResponse = array(
            'app' => APP_NAME,
            'url' => URL,
            'type' => 'API',
            'version' => 'v1',
            'status' => 'running'
        );

        $this->render('index/index', $genericApiResponse);
    }
}

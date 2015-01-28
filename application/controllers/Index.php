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
        $this->render('index/index');
    }
}

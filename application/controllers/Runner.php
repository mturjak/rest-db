<?php

/**
 * Runner controller
 * executes operations/scripts
 */
class Runner extends Controller
{
    /**
     * Generic runner response
     */
    public function index()
    {
        $this->render('classes/index', array(
            'message' => "Nothing to run.",
            'result' => false
        ));
    }

    /**
     * Runs scripts on inpit files
     */
    public function run()
    {
        if(!empty($this->app->request()->post('scripts'))) {
            $res = array(
                'input' => $this->app->request()->post('input'),
                'scripts' => $this->app->request()->post('scripts'),
                'output' => "output_folder",
                'timestamp' => time()
            );

            $this->render('classes/index', array(
                'message' => "Running...",
                'result' => $res
            ), 202);
        } else {
            $this->index();
        }
    }
}
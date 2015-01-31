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
     * Runs scripts on inpit files - creates runner
     */
    public function run()
    {
        if(!empty($this->app->request()->post('scripts'))) {

            $run = array(
                'input' => $this->app->request()->post('input'),
                'scripts' => $this->app->request()->post('scripts')
            );

            $runner_id = sha1(serialize($run));

            if(file_exists(RUNNER_OUTPUT_PATH . $runner_id)) {
                $this->halt(400, 'Runner already exists.');
            }

            $run_add = array(
                'runner_id' => $runner_id,
                'timestamp' => time()
            );

            $this->render('classes/index', array(
                'message' => "Running...",
                'result' => array_merge($run, $run_add)
            ), 202);
        } else {
            $this->index();
        }
    }

        /**
     * Reruns existing runner
     */
    public function reRun($runner_id)
    {
        if(!empty($runner_id) && !empty($this->app->request()->put('scripts'))) {

            $run = array(
                'input' => $this->app->request()->post('input'),
                'scripts' => $this->app->request()->post('scripts')
            );

            if(file_exists(RUNNER_OUTPUT_PATH . $runner_id)) {
                $this->halt(404, "Runner \"$runner_id\" does not exist on server.");
            } elseif($runner_id !== sha1(serialize($run))) {
                $this->halt(400, "Post data does not match runner.");
            }

            $run_add = array(
                'output' => $runner_id,
                'timestamp' => time()
            );

            $this->render('classes/index', array(
                'message' => "Running...",
                'result' => $run_add
            ), 202);
        } else {
            $this->index();
        }
    }
}
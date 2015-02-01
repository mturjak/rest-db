<?php

class RunnerModel {

    /**
     *
     */
    public function run()
    {
        return $this->createRunner();

        // TODO: load service, make runner folder, save runner file ".runner.json", run service, add db reccord

        /*$db = Database::getInstance();

        $query = $db->prepare("SELECT id as _id, CONCAT(server , path) as path, title, FROM_UNIXTIME(created) as added, user_name as added_by FROM files LEFT JOIN users ON files.created_by = users.user_id LIMIT 10");
        $query->execute();
        $res = $query->fetchAll();
        if(count($res) > 0) {
            return $res;
        } else {
            return [];
        }
        return false;*/
    }

    private function createRunner()
    {
        $app = \Slim\Slim::getInstance();
        $req = $app->request();

        if(!empty($req->post('scripts'))) {

            if(empty($service = $req->post('service'))) {
                $service = RUNNER_SERVICE; // use default service
            }

            $run = array(
                'input' => $req->post('input'),
                'scripts' => $req->post('scripts'),
                'service' => $service,
                'location' => 'r-server' // TODO: make it possible to choose where to run service (localy, different servers, ...)
            );

            $runner_id = sha1(serialize($run));

            // TODO: move this to controller and base on model response ??
            if(file_exists(RUNNER_OUTPUT_PATH . $runner_id)) {
                $this->halt(400, 'Runner already exists.');
            }

            $run_add = array(
                'runner_id' => $runner_id,
                'timestamp' => time()
            );

            return array_merge($run, $run_add);
        } else {
            return false;
        }
    }

}
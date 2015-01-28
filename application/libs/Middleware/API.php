<?php
/**
 * IsAPI
 * Checks if the request is through the API or for the html page
 */
namespace Middleware;

class API extends \Slim\Middleware
{
    /**
     * Call
     */
    public function call()
    {
        $app = $this->app;
        $req = $app->request();
        $env = $app->environment();

        $res_uri = explode('/', $req->get('url'), 2);

        //$media_type = trim($req->getMediaType());

        if($res_uri[0] === 'api') {
            $env['PATH_INFO'] = '/' . (isset($res_uri[1]) ? $res_uri[1] : '');
        }

        // set response content-type to JSON for all requests
        $app->contentType('application/json; charset=UTF-8');

        // request info for debugging purposes
        $app->req_str = "[method: {$req->getMethod()}] [url: {$req->getResourceUri()}]";
        $this->next->call();
    }
}
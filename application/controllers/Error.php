<?php
/**
 * Error controller
 * This controller shows custom error pages
 */
class Error extends Controller
{
    // TODO: error redirects instead of direct rendering (so we don't break the template)
    // TODO: error codes !== HTTP response codes

    /**
     * This method controls what happens / what the user sees when an notFound error occures (404)
     */
    function notFound()
    {
        $response["error"] = true;
        $response["message"] = "Resource not found on server.";

        $this->render('error', $response, 404);
    }

    /**
     * This method controls what happens when an error occures
     * @param string $message custom error message (extracted from $e->getMessage())
     * @param int $code HTTP response code (extracted from $e->getCode())
     */
    function genericError($message = '', $code = 500)
    {
        if($message === '') {
            $message = 'A website error has occurred. The website administrator has been notified of the issue. Sorry for the temporary inconvenience.';
        }

        $response["error"] = true;
        $response["message"] = $message;

        // render without page header and footer because the error can be cought after page header already set
        $this->render('error', $response, $code);
    }
}

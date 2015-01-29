<?php

/**
 * Files controller
 * 
 */
class Files extends Classes
{
    /**
     * Lists objects of class $name (records from data table)
     * @param string $name Class name
     */
    public function listFiles()
    {
        $model = $this->loadModel('File');
        $result = $model->listFiles();

        if($result === false) {
            $this->app->notFound();
        }

        $this->render('classes/index', array(
            'message' => "List of files.",
            'count' => count($result) ? $model->rowsCount('files') : 0,
            'result' => $result
        ));
        //$error = 'Always throw this error';
        //throw new Exception($error, 400);
    }

    /**
     * Lists objects of class $name (records from data table)
     * @param string $name Class name
     */
    public function getFile($id)
    {
        $model = $this->loadModel('File');
        $result = $model->getFile($id);

        if(!$result) {
            $this->app->notFound();
        }

        $this->render('classes/view', array(
            'message' => "File with fileId = {$id}.",
            'result' => $result
        ));
        //$this->halt(300);
    }

    /**
     * Adds new file
     */
    public function addFile()
    {
        $model = $this->loadModel('File');
        if($model->addFile()) {
            $message = "New file added successfully.";
            $code = 201;
        } else {
            $message = "Error adding file.";
            $code = 500;
        }


        $this->render('classes/view', array(
            'message' => $message
        ), $code);
    }
}
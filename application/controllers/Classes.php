<?php

/**
 * Classes controller
 * 
 */
class Classes extends Controller
{
    /**
     * Default classes response - lists accessable classes (data tables)
     */
    public function index()
    {
        //$this->app->flashNow('info', 'Your credit card is expired');
        $model = $this->loadModel('Class');
        $result = $model->listClasses();

        $this->render('classes/index', array(
            'message' => 'List of all accessable classes.',
            'count' => $model->rowsCount('classes'),
            'result' => $result
        ));
    }

    /**
     * Lists objects of class $name (records from data table)
     * @param string $name Class name
     */
    public function items($name)
    {
        $model = $this->loadModel('Class');
        $result = $model->listItems($name);

        if(!$result) {
            $this->app->notFound();
        }

        $this->render('classes/index', array(
            'message' => "List of objects of class \"{$name}\".",
            'count' => $model->rowsCount($name),
            'result' => $result
        ));
        //$error = 'Always throw this error';
        //throw new Exception($error, 400);
    }

    /**
     * Lists objects of class $name (records from data table)
     * @param string $name Class name
     */
    public function show($name, $id)
    {
        $model = $this->loadModel('Class');
        $result = $model->showItem($name, $id);

        if(!$result) {
            $this->app->notFound();
        }

        $this->render('classes/view', array(
            'message' => "Single object of class \"{$name}\" with ObjectId = {$id}.",
            'result' => $result
        ));
        //$this->halt(300);
    }

    /**
     * Adds new item
     * @param string $name Class name
     */
    public function addItem($name)
    {
        $model = $this->loadModel('Class');
        if($model->addItem($name)) {
            $message = "New object of class \"{$name}\" created successfully.";
            $code = 201;
        } else {
            $message = "Error adding new object.";
            $code = 500;
        }


        $this->render('classes/view', array(
            'message' => $message
        ), $code);
    }
}
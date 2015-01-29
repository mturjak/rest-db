<?php
/**
 * Routes
 * Slim route deffinitions (mapping: API request -> controller/action)
 * required inside custom Router class ($this <=> Router instance)
 *
 */


/* API rautes */

/************* classes group **************/

$app->group('/classes', function() use($app) {


  /**************  GET  ***************/

  /**
   * List classes
   */
  $app->get('(/$|/index$|$)', 'Middleware\Auth::authBase', function () {
    $this->loadController('classes', 'index');
  });

  /**
   * List records
   */
  $app->get('/:name(/$|/index$|$)', 'Middleware\Auth::authBase', function ($name) use($app) {
    if($name === 'index') {
      $app->redirect(URL . 'classes' . '/');
    }
    $this->loadController('classes', 'items', $name);
  });

  /**
   * Show record
   */
  $app->get('/:name/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authBase', function ($name,$id) use($app) {
    if($name === 'index' || $id === 'index') {
      $app->redirect(URL . 'classes' . '/');
    }
    $this->loadController('classes', 'show', $name, $id);
  });


  /**************  POST  ***************/

  /**
   * Create object / create class if not exists
   */
  $app->post('/:name(/$|/index$|$)', 'Middleware\Auth::authSession', function ($name) use($app) {
    if($name === 'index' || $name === '') {
      $app->notFound();
    } else {
      $this->loadController('classes', 'addItem', $name);
    }
  });

  /**************  PUT  ***************/

  /**
   * Update record
   */
  $app->put('/:name/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($name,$id) use($app) {
    if($name === 'index' || $id === 'index') {
      $app->redirect(URL . 'classes' . '/');
    }
    $this->loadController('classes', 'edit', $name, $id);
  });

  /**************  DELETE  ***************/

  /**
   * Delete record
   */
  $app->delete('/:name/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($name,$id) use($app) {
    if($name === 'index' || $id === 'index') {
      $app->halt(105, 'Problem deleting!');
    }
    $this->loadController('classes', 'delete', $name, $id);
  });
}); /*** classes group END ***/


/************* file routes **************/ // TODO: combine with classes by adding conditions
$app->group('/files', function() use($app) {

  /**
   * List files
   */
  $app->get('(/$|/index$|$)', 'Middleware\Auth::authBase', function () {
    $this->loadController('files', 'listFiles');
  });

  /**
   * Show record
   */
  $app->get('/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authBase', function ($id) use($app) {
    if($id === 'index') {
      $app->redirect(URL . 'files/');
    }
    $this->loadController('files', 'getFile', $id);
  });


  /**************  POST  ***************/

  /**
   * Create object / create class if not exists
   */
  $app->post('(/$|/index$|$)', 'Middleware\Auth::authSession', function () {
      $this->loadController('files', 'addFile');
  });

  /**************  PUT  ***************/

  /**
   * Update record
   */
  $app->put('/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($id) use($app) {
    if($id === 'index') {
      $app->redirect(URL . 'files' . '/');
    }
    $this->loadController('files', 'edit', $name, $id);
  });

  /**************  DELETE  ***************/

  /**
   * Delete record
   */
  $app->delete('/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($id) use($app) {
    if($id === 'index') {
      $app->halt(105, 'Problem deleting!');
    }
    $this->loadController('files', 'delete', $id);
  });
});

/************* users group **************/
$app->group('/users', function() use($app) {


  /**************  GET  ***************/

  /**
   * List users
   */
  $app->get('(/$|/index$|$)', 'Middleware\Auth::authSession', function () {
      $this->loadController('users', 'index');
  });

  /**
   * Show user
   */
  $app->get('/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($id) use($app) {
    if($id === 'index') {
      $app->redirect(URL . 'users/');
    }
    $this->loadController('users', 'show', $id);
  });


  /**************  POST  ***************/

  /**
   * Create user / Sign up
   */
  $app->post('(/$|/index$|$)', function () {
    $this->loadController('users', 'addUser');
  });

  /**************  PUT  ***************/

  /**
   * Update user
   */
  $app->put('/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($id) use($app) {
    if($id === 'index') {
      $app->redirect(URL . 'users/');
    }
    $this->loadController('users', 'edit', $id);
  });

  /**************  DELETE  ***************/

  /**
   * Delete user
   */
  $app->delete('/:id(/$|/index(/|$)|$)', 'Middleware\Auth::authSession', function ($id) use($app) {
    if($id === 'index') {
      $app->halt(105, 'Problem deleting: id not set!');
    }
    $this->loadController('users', 'delete', $id);
  });
}); /*** users group END ***/


/**************** users related rules *********/

$app->post('/login(/:name$|/index$|$)', function () {
  $this->loadController('users', 'login');
});

$app->get('/logout(/$|/index$|$)', 'Middleware\Auth::authSession', function () {
  $this->loadController('users', 'logout');
});

$app->post('/requestPasswordReset(/$|/index$|$)', function () {
  $this->loadController('users', 'requestPasswordReset');
});

$app->get('/verify/:name/:uid/:code(/$|/index$|$)', function ($name, $uid, $code) {
  $this->loadController('users', 'verify_' . $name, $uid, $code);
});

/****************  errors  *****************/

/**
 * 404 - Not Found
 */
$app->notFound(function() {
  $this->loadController('error', 'notFound');
});

// for other errors
$app->error(function(\Exception $e) {
  $this->loadController('error', 'genericError', $e->getMessage(), $e->getCode());
});

// error page
$app->get('/error(/$|/index$|$)', function () {
  $this->loadController('error','genericError');
});


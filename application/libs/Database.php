<?php

/**
 * Class Database
 * Creates a PDO database connection. This connection will be passed into the models (so we use
 * the same connection for all models and prevent to open multiple connections at once)
 */
class Database extends PDO
{
    private static $instance;
    private $database;

    public static function getInstance()
    {
        if (!self::$instance)
            self::$instance = new Database();
        return self::$instance;
    }

    public function __construct() {
        if (!$this->database) {
            $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);
            $this->database = parent::__construct(
                DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT . ';charset=' . DB_CHARSET,
                DB_USER, DB_PASS, $options
            );
        }
        return $this->database;
    }
}

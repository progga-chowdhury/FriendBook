<?php

//This is a singleton class, only one database connection is ever created and every
//DataSet class shares it through the getInstance() method instead of opening
//its own connection.
class Database
{
    /**
     * @var Database
     */
    protected static $_dbInstance = null;

    /**
     * @var PDO
     */
    protected $_dbHandle;

    /**
     * @return Database
     */
    public static function getInstance()
    {

        if (self::$_dbInstance === null) { //checks if the PDO exists
            // creates new instance if not, sending in connection info
            self::$_dbInstance = new self();
        }
        return self::$_dbInstance;
    }

    //private constructor so that nothing outside the class can create a second connection with
    //"new Database()"
    private function __construct()
    {
        try {                               //use of (__DIR__): the path is built from this file's own location
            //instead of a machine specific path, so it works on any pc.
            $this->_dbHandle = new PDO("sqlite:" . __DIR__ . "/../Database/friendbook.sqlite");

        } catch (PDOException $e) { // catch any failure to connect to the database
            echo $e->getMessage();
        }
    }

    /**
     * @return PDO
     */
    public function getdbConnection()
    {
        return $this->_dbHandle; // returns the PDO handle to be used elsewhere
    }

    public function __destruct()
    {
        $this->_dbHandle = null; // destroys the PDO handle when no longer needed
    }
}

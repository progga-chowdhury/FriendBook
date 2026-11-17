<?php

require_once("Database.php");

//The abstract parent class for the DataSet classes (like UserDataSet and FriendDataSet), it
// gets the shared database connection once here, so that the child classes don't have to
//repeat it and can just use $this->_dbHandle in their queries instead. This class is abstract because
//it only exists to be extended later, it's never used with "new DataSet()".

abstract class DataSet
{

    protected $_dbHandle; //the PDO connection, protected so child classes can use it.
    protected $_dbInstance; //the Database singleton that the PDO connection came from.

    //Constructor that runs automatically when a child class is created with "new UserDataSet()", it gets
    //the single shared Database object and then pulls the PDO connection out of it.
    public function __construct()
    {
        $this->_dbInstance = Database::getInstance();
        $this->_dbHandle = $this->_dbInstance->getdbConnection();
    }
}




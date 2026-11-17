<?php

//Model class for one row of the friends table, a single friendship or a friend request between
//two users. It holds the data and has getters only, all the SQL lives inside of FriendDataSet.
class FriendData
{

    //friend 1 is the user who sent the friend request, friend 2 is the receiver, status 1 equals
    //to a pending request and  status 2 is an accepted request. (which then makes them friends.)
    protected $_id, $_friend1, $_friend2, $_status;

    //The constructor takes one row from the database and copies its values into the
    //fields.
    public function __construct($dbRow)
    {
        $this->_id = $dbRow["id"];
        $this->_friend1 = $dbRow["friend1"];
        $this->_friend2 = $dbRow["friend2"];
        $this->_status = $dbRow["status"];
    }

    //These are all the getter methods, each field is protected so that other files read them
    //through these methods instead.
    public function getId()
    {
        return $this->_id;
    }

    public function getFriend1()
    {
        return $this->_friend1;
    }

    public function getFriend2()
    {
        return $this->_friend2;
    }

    public function getStatus()
    {
        return $this->_status;
    }


}
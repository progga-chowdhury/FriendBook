<?php

require_once ('DataSet.php');
require_once ('UserData.php');

//This class handles all the SQL for the users table. It extends DataSet to get the shared
//database connection. Every query in this class is a prepared statement, the SQL is written
//with placeholders and the values are sent separately, therefore the user input can never change the
//meaning of the query. This stops any SQL injection.
//Each row found is also turned into a UserData Object.

class UserDataSet extends DataSet {

   //This method finds one user by their username (used by the login page). It
   //returns a UserData object, or it returns null if there is no user.
    public function fetchUserByUsername($username)
    {
        $sqlQuery = 'SELECT * FROM users WHERE username = :username';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([':username' => $username]);


        $row = $statement->fetch();

            if ($row == true) {
                return new UserData($row);
            }
            return null;
    }

    //This method gets one page of users for the users list. The $limit parameter is how many to
    //display per page, $offset is how many users to skip. For example, page 3 with 10 users displayed per
    //page would skip 20 users. As 20 were displayed as 10 users per page 1 and 10 users per page 2.
    //$search matches usernames that start with the typed text with % added after it, which is "LIKE" in SQL. (eg "Lee/Henry" -> LIKE "Lee%"/"Henry%").
    //The excludedId leaves out the logged-in user so they can't see themselves in the list.
    //LIMIT and OFFSET are bound with PDO: :PARAM_INT so they're sent as real whole numbers,
    //When they pass through execute([]) it would send them as text.
    public function fetchUserPaging($limit, $offset, $search = "", $excludedId = 0) {
        $sqlQuery = 'SELECT * FROM users WHERE username LIKE :search AND id != :excludedId
                     LIMIT :limit OFFSET :offset';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->bindValue(':search', $search . "%");
        $statement->bindValue(':excludedId', $excludedId, PDO::PARAM_INT);
        $statement->execute();

        $dataSet = [];
        while ($row = $statement->fetch()) {
            $dataSet[] = new UserData($row);
        }
        return $dataSet;
    }

    //This method counts the users that match the search, leaving out the logged-in user (the same filters
    //used by the method fetchUserPaging(). The paging uses this total to figure out how many pages there are all
    //together. The COUNT (*) function returns one number which then fetchColumn() reads and allows the type casting (int)
    //to turn it into an integer.
    public function countUsers($search = "", $excludedId = 0){
        $sqlQuery="SELECT COUNT(*) FROM users WHERE username LIKE :search AND id != :excludedId";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([':search' => $search . "%", ':excludedId' => $excludedId]);
        return (int)$statement->fetchColumn();


    }

    //Finds one user by their id, returns a UserData object or returns null
    //if not found.
    public function fetchUserById($id) {
        $sqlQuery = 'SELECT * FROM users WHERE id = :id';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([':id' => $id]);

        $row = $statement->fetch();
        if ($row == true) {
            return new UserData($row);
        }
        return null;
    }

    //This method returns true if another user already has this username, this
    //is used by the edit profile page to stop any duplicates. The SQL COLLATE NOCASE
    //makes the comparison of the 2 usernames ignore any upper/lowercase letters. Eg "Henry" and "henry"
    //count as the same username. The id != :excludedId leaves out the user's own row in the database, therefore
    //saving without changing the existing username isn't flagged.
    public function userNameAlreadyExists($username,$excludedId) {
        $sqlQuery = 'SELECT COUNT (*) FROM users 
                     WHERE username = :username COLLATE NOCASE
                     AND id != :excludedId';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([':username' => $username, ':excludedId' => $excludedId]);
        return $statement->fetchColumn() > 0;
    }

    //This method updates the username, email and picture for one user. The WHERE id = :id
    //makes sure that only that specific user's row changes as without it, every user row would end
    //up being updated.
    public function profileUpdate($id, $username, $email, $photo) {
        $sqlQuery = 'UPDATE users
                     SET username = :username, email = :email, photo = :photo
                     WHERE id = :id';
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([':username' => $username, ':email' => $email, ':photo' => $photo, ':id' => $id]);
    }





}

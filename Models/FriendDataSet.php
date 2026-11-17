<?php

require_once("DataSet.php");
require_once("FriendData.php");

//This class handles all the SQL for the friends table. It extends DataSet to get the shared
//database connection. Every query uses a prepared statement with placeholders like (:name) and execute([]), so that
//the user input is never actually put straight into the SQL, this stops any SQL injection attempt.

class FriendDataSet extends DataSet
{

    //This method looks for a friendship/request between two users, (in both A sends a request to B, and B sends one
    //to A). It then returns a FriendData object if one exists, otherwise it returns null.
    public function fetchFriendships($userA, $userB)
    {
        $sqlQuery = "SELECT * FROM friends 
                     WHERE (friend2 = :userTypeA AND friend1 = :userTypeB)
                     OR (friend2 = :userTypeB AND friend1 = :userTypeA)";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":userTypeA" => $userA, ":userTypeB" => $userB]);

        $row = $statement->fetch();
        if ($row == true) {
            return new FriendData($row);
        }
        return null;
    }

    //Checks if userA and userB have an existing request or friendship as true/false values. True
    //if userA and userB already have a request/friendship, false if otherwise.
    //This way any duplicates cannot be created.
    public function existingFriendships($userA, $userB)
    {
        $friendship = $this->fetchFriendships($userA, $userB);

        if ($friendship != null) {
            return true;
        }
        return false;
    }

    //Creates a pending request of status 1, returns false if the user tries to add
    //themselves or if a request/friendship already exists.
    public function sendFriendRequests($senderId, $receiverId)
    {
        if ($senderId == $receiverId) {
            return false;
        }

        if ($this->existingFriendships($senderId, $receiverId)) {
            return false;
        }

        $sqlQuery = "INSERT INTO friends (friend1,friend2,status) VALUES (:senderId,:receiverId,1)";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":senderId" => $senderId, ":receiverId" => $receiverId]);
        return true;
    }

    //Gets every row where the user either sent or received a friend request, with the
    //newest information first. Each row then is turned into a FriendData object which the controller
    //friends.php splits into incoming, outgoing, and friend categories in the friendlist.
    public function fetchFriendListInfo($userId)
    {
        $sqlQuery = "SELECT * FROM friends 
                     WHERE friend1 = :userId OR friend2 = :userId
                     ORDER BY id DESC";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":userId" => $userId]);

        $dbRows = [];
        while ($dbRow = $statement->fetch()) {
            $dbRows[] = new FriendData($dbRow);
        }
        return $dbRows;


    }

    //Accepts a friend request, allowing the status to change from 1 to 2. The WHERE checks
    //whether friend2 equals to the userId so that only the receiver can accept the request.
    //The status = 1 so that this can only be done to pending requests, this is also
    //checked in the SQL so changing a requestId in the form wouldn't let someone accept another user's request.
    //At the end of the method, the value true is returned if a row was changed, or it returns
    //false if nothing was matched.
    public function acceptFriendRequest($requestId, $userId)
    {

        $sqlQuery = "UPDATE friends SET status = 2
                     WHERE id = :requestId AND friend2 = :userId AND status = 1";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":requestId" => $requestId, ":userId" => $userId]);
        return $statement->rowCount() > 0;

    }

    //This method declines a request by deleting it. Checks for only the receiver as they are the only
    //person who can decline the friend request. The status needs to stay pending for this to happen. (status=1)
    public function declineFriendRequest($requestId, $userId)
    {
        $sqlQuery = "DELETE FROM friends
                     WHERE id = :requestId AND friend2 = :userId AND status = 1";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":requestId" => $requestId, ":userId" => $userId]);
        return $statement->rowCount() > 0;
    }

    //This method cancels a request that a user already has sent, so only the sender (friend1) can cancel it
    //if it is still pending.
    public function cancelFriendRequest($requestId, $userId)
    {
        $sqlQuery = "DELETE FROM friends
                     WHERE id = :requestId AND friend1 = :userId AND status = 1";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":requestId" => $requestId, ":userId" => $userId]);
        return $statement->rowCount() > 0;
    }

    //This method counts the number of accepted friends for a user regardless of whether they
    //are a sender or a receiver. The COUNT(*) function gives one number that the fetchColumn() method
    //then reads. The type casting (int) makes sure that it is an integer being processed.
    public function friendCount($userId)
    {
        $sqlQuery = "SELECT COUNT(*) FROM friends
                     WHERE (friend1 = :userId OR friend2 = :userId) AND status = 2";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":userId" => $userId]);
        return (int)$statement->fetchColumn();
    }

    //This method allows the user to unfriend any accepted friendships. Both the sender and receiver
    //are able to do this.
    public function removeExistingFriend($requestId, $userId)
    {
        $sqlQuery = "DELETE FROM friends
                     WHERE id = :requestId 
                     AND (friend1 = :userId OR friend2 = :userId) 
                     AND status = 2";
        $statement = $this->_dbHandle->prepare($sqlQuery);
        $statement->execute([":requestId" => $requestId, ":userId" => $userId]);
        return $statement->rowCount() > 0;
    }


}
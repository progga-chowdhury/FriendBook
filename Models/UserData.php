<?php
//This is a model class for one row of the users table containing one user's data,
//it only holds the data and contains getter methods, all the SQL lives inside
//UserDataSet.
class UserData
{
    protected $_id;
    protected $_username;
    protected $_email;
    protected $_photo;
    protected $_password;
    protected $_lat;
    protected $_lng;

    //The constructor takes one database row and copies each value into a field.
    public function __construct($dbRow){
        $this->_id = $dbRow["id"];
        $this->_username = $dbRow["username"];
        $this->_email = $dbRow["email"];
        $this->_photo = $dbRow["photo"];
        $this->_password = $dbRow["password"];
        $this->_lat = $dbRow["lat"];
        $this->_lng = $dbRow["lng"];
    }

    //The following methods are getters. The fields are all protected so that other files can
    //read them through these methods instead.
    public function getId(){
        return $this->_id;
    }
    public function getUsername(){
        return $this->_username;
    }
    public function getEmail(){
        return $this->_email;
    }

    //The raw file name that is stored in the
    // database, e.g. "3.jpg" or "face5.jpg".
    public function getPhoto(){
        return $this->_photo;
    }

    //The password hash used by password_verify method.
    public function getPassword(){
        return $this->_password;
    }

    //The latitude and longitude stored inside the table but not currently displayed.
    public function getLat(){
        return $this->_lat;
    }
    public function getLng(){
        return $this->_lng;
    }

    //This method returns the path of the profile picture to put in an <img> tag,
    //the basename() method strips any folder parts from the stored name so that it
    //can only point at a file inside the profiles/ folder. file_exists() then checks that the
    //picture is actually present, but if it's absent or there isn't a filename for it, it will
    //instead return a placeholder image so that the site never shows a broken image.
    public function getPhotoPath(){
        $file = basename((string)$this->_photo);
        if ($file !== '' && file_exists(__DIR__ . '/../profiles/' . $file)){
           return 'profiles/' . $file;
        }
        return 'profiles/placeholder.jpg';
    }

}
<?php
class ApacheUser{
    //This requires both a basic htpasswd file and a .userlist file that contains the written name
    //and email address of the users authorized by htpasswd. It assumes the username is their email.
    private $user_file = '.userlist';
    private $email = null;
    private $name = null;

    public function __construct(){
        $this->check_basic_login();
    }

    private function check_basic_login(){
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="MEP"');
            header('HTTP/1.0 401 Unauthorized');
            die('Please log in to access this page.');
        } else {
            $this->read_user($_SERVER['PHP_AUTH_USER']);
        }
    }

    private function read_user($email){
        $fn = fopen('./.userlist', 'r');
        while($data_row = fgetcsv($fn)){
            if($data_row[0]==$_SERVER['PHP_AUTH_USER']){
                $this->set_attribute('email', $data_row[0]);
                $this->set_attribute('name', $data_row[1]);
                fclose($fn);
                return true;
            }
        }
        fclose($fn);
        throw new Exception('User not found.');
    }

    //Read an attribute about this user.
    public function get_attribute($key){
        if(isset($this->$key)){
            return $this->$key;
        } else {
            return false;
        }
    }

    //Assign an attribute
    public function set_attribute($key, $val){
        $this->$key = $val;
    }
}
?>
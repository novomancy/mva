<?php
/**
 * DNDUser class
 * Mostly a wrapper for looking up a user in the DND and neatly encapsulating the data
 * returned from LDAP
 */
class DNDUser{
    //configure connection to the LDAP server
    private $ldap_address = "oud.dartmouth.edu";

    // In the optional attribute array, we need to specify the attributes exactly as recorded on the server
    // e.g. $sr=ldap_search($ds, "dc=dartmouth,dc=edu", "cn=".$a["cn"], array("cn","dcDeptclass","uid") );  
    // If no attribute array, we get all of them.
    private $ldap_base = "dc=dartmouth,dc=edu";
    private $ldap_filter = ""; //filled later based on template
    private $ldap_filter_template = "(&(cn=<<<name>>>)(!(edupersonPrimaryAffiliation=Alum)))"; //<<<name>>> will be replaced

    //public data
    public $cn = "";
    public $attributes = array();

    //$cn should be a unique username that is returned from CAS
    public function __construct($cn){
		$name_only = preg_split("/@/", $cn);
		$name_only = $name_only[0];
        $this->set_name($name_only);
    }

    //Set the name (and thus reset the ldap filter string)
    public function set_name($cn){
        $this->cn = $cn;
        $this->ldap_filter = preg_replace('/(<<<name>>>)/', $this->cn, $this->ldap_filter_template);
    }

    //Call this function to translate attributes array from LDAP-speak to something more understandable
    public function humanize(){
        if(count($this->attributes) == 0){
            return false;
        }
        $this->set_attribute('firstname', $this->get_attribute('givenname'));
        $this->set_attribute('lastname', $this->get_attribute('sn'));
        $this->set_attribute('email', strtolower($this->get_attribute('mail')));
        $this->set_attribute('name', $this->get_attribute('cn'));
        $this->set_attribute('deptclass', $this->get_attribute('dcdeptclass'));
        $this->set_attribute('uniqueid', strtolower($this->get_attribute('dcdnduid')));
        $this->set_attribute('netid', strtolower($this->get_attribute('uid')));
        $this->set_attribute('affiliation', $this->get_attribute('edupersonprimaryaffiliation'));
    }

    //Read an attribute about this user. $key maps to whatever field names came out of LDAP
    public function get_attribute($key){
        if(isset($this->attributes[$key])){
            return $this->attributes[$key];
        } else {
            return false;
        }
    }

    //Assign an attribute that may or may not appear in the LDAP data
    public function set_attribute($key, $val){
        $this->attributes[$key] = $val;
    }

    //Run a lookup with the currently set name
    public function do_lookup(){
        //validate settings
        if($this->cn == ''){
            throw new Exception('Username not set before LDAP lookup');
            return false;
        }
        $ds = ldap_connect($this->ldap_address);
        if(!$ds){
            throw new Exception('Could not connect to LDAP server');
            return false;
        }

        //Do the search. If we get more than one result, something is horribly wrong
        $results = ldap_search($ds, $this->ldap_base, $this->ldap_filter);
        $ct = ldap_count_entries($ds, $results);
        if($ct > 1){
            throw new Exception('Provided username is not unique');
            ldap_close($ds);
            return false;
        } else if ($ct==0){
            throw new Exception('User not found');
            ldap_close($ds);
            return false;
        }

        //Unpack results
        $this->attributes = array();    //clear any old data
        $records = ldap_get_entries($ds, $results);
        $user = $records[0];

        $numattr=$user["count"];
        for ($i=0; $i<$numattr; $i++) {
            $this->attributes[$user[$i]] = $user[$user[$i]][0];
        }

        ldap_close($ds);
    }
}
?>
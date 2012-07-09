<?php

namespace Consegna\ElaboratiBundle;
use adLdap;

class Studente 
{

    protected $username;
    protected $password;
    protected $classe;
    protected $adConfig;
    protected $ldap;
    protected $memberOf;
    protected $firstname;
    protected $lastname;

    public function __construct($adConfig = false,$username=false,$password=false) {
        $adConfig['account_suffix'] = '@' . $adConfig['account_suffix'];
        $this->adConfig=$adConfig;
        $this->ldap = new adLDAP($this->adConfig);
        $this->ldap->connect();
        if (($username) and ($password)) {
            $this->checkPassword($username, $password);
        }
    }

    public function checkPassword($username, $password) {
        $this->username=$username;
        $this->password=$password;
                return $this->ldap->authenticate($this->username, $this->password);
    }

    public function getMemberOf() {
        if ($this->memberOf == false) {
            $memberOf = false;
            while ($memberOf == false) {
                $info = $this->ldap->user_info($this->username);
                if (($info!=false) and (array_key_exists(0, $info))) {
                    if (array_key_exists('memberof', $info[0])) {
                        $memberOf = $info[0]['memberof'];
                    }
                }
            }
            $this->memberOf = $memberOf;
        }
        return $this->memberOf;
    }

    public function getClasse() {
        if ($this->classe == false) {
            $classe=false;
            foreach ($this->getMemberOf() as $cn) {
                if (substr($cn, 0, 4) == "CN=_") {
                    $classe = substr($cn, 4, strpos($cn,',',4)-4);
                    break;
                }
            }
            $this->classe = $classe;
        }
        return $this->classe;
    }

    
        public function getFirstname() {
            $this->firstname='Utente';
        return $this->firstname;
    }
    
    public function getLastname() {
            $this->firstname='Test';
        return $this->firstname;
    }
}

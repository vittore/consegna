<?php

namespace Consegna\ElaboratiBundle\Tests;
use Consegna\ElaboratiBundle\Studente;


class StudenteTest extends \PHPUnit_Framework_testCase
{
    
    private $adConfig;
    
    public function __construct() {
        $this->adConfig=array ( 
            'account_suffix' => 'issm2',
            'base_dn' => 'DC=issm2', 
            'domain_controllers' => array (  '10.10.176.1' ),
            'real_primarygroup' => 1,
            'use_ssl' => false);
    }
    
    public function testCheckPasswordWithBadUsername()
    {
        $username="tizio";
        $password="caio";
        $studente = new Studente($this->adConfig);
        $this->assertFalse($studente->checkPassword($username,$password));
        
    }
    public function testCheckPasswordWithBadPassword()
    {
        $username="utente.test";
        $password="caio";
        $studente = new Studente($this->adConfig);
        $this->assertFalse($studente->checkPassword($username,$password));
        
    }
    public function testCheckPasswordWithGoodPassword()
    {
        $username="utente.test";
        $password="mypassword";
        $studente = new Studente($this->adConfig);
        $this->assertTrue($studente->checkPassword($username,$password));
        
    }
    
    public function testGetMemberOf()
    {
        $username="utente.test";
        $password="mypassword";
        $studente = new Studente($this->adConfig);
        $studente->checkPassword($username,$password);
        $this->assertTrue($studente->getMemberOf()==array(
    'count' => 5,
    0 => 'CN=_IUSVE,DC=issm2',
    1 => 'CN=igroove_users,DC=issm2',
    2 => 'CN=internetWireless,DC=issm2',
    3 => 'CN=internetLAN,DC=issm2',
    4 => 'CN=Domain Users,CN=Users,DC=issm2'
));
        
    }
    
        public function testGetClasse()
    {
        $username="utente.test";
        $password="mypassword";
        $studente = new Studente($this->adConfig);
        $studente->checkPassword($username,$password);
        $this->assertTrue($studente->getClasse()=='IUSVE');
    }
    
    
        
        public function testGetClasseSingleLine()
    {
        $username="utente.test";
        $password="mypassword";
        $studente = new Studente($this->adConfig,$username,$password);
        $this->assertTrue($studente->getClasse()=='IUSVE');
    }

            public function testGetName()
    {
        $username="utente.test";
        $password="mypassword";
        $studente = new Studente($this->adConfig,$username,$password);
        $this->assertTrue($studente->getFirstname()=='Utente');
        $this->assertTrue($studente->getLastname()=='Test');
    }
    
}

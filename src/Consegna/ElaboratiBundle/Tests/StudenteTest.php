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
        $username="studentUser";
        $password="caio";
        $studente = new Studente($this->adConfig);
        $this->assertFalse($studente->checkPassword($username,$password));
        
    }
    public function testCheckPasswordWithGoodPassword()
    {
        $username="v.zen";
        $password="Zne22vtr";
        $studente = new Studente($this->adConfig);
        $this->assertTrue($studente->checkPassword($username,$password));
        
    }

    
}

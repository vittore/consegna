<?php

namespace Consegna\ElaboratiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('a')->selectLink('consegna')->count() > 0, "Consegna link found on page");
    }
    
    
    public function testCheckPasswordAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/consegna');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        
        
        // reject uncorrect username/password
        
        // accept correct username and password
    }
}

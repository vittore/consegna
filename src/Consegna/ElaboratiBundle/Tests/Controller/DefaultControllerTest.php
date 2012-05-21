<?php

namespace Consegna\ElaboratiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

         
        $crawler = $client->request('GET', '/');
        
        
         $crawler = $crawler->filter('img');
         foreach ($crawler as $elementoDom) {
    print $elementoDom->nodeValue;
}

     //   $this->assertEquals('consegna', $link->getUri(), "Consegna link found on page");
    }
}

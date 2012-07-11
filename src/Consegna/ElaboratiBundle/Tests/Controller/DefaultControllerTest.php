<?php

namespace Consegna\ElaboratiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase {

    public function testIndex() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('a')->selectLink('consegna')->count() > 0, "Consegna link found on page");
    }

    public function testCheckPasswordAction() {
        $client = static::createClient();
        $username = "";
        $password = "mypassword";
        $crawler = $client->request('GET', '/checkPassword?username=' . $username . "&password=" . $password);
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals($client->getResponse()->getContent(), '{"stato":false}');
        $username = "utente.test";
        $password = "";
        $crawler = $client->request('GET', '/checkPassword?username=' . $username . "&password=" . $password);
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals($client->getResponse()->getContent(), '{"stato":false}');
        $username = "utente.test";
        $password = "mypassword";
        $consegnaConfig = $client->getContainer()->getParameter('consegna_elaborati');
        $dirConsegna = $consegnaConfig['cartella'] . "/IUSVE";
        if (file_exists($consegnaConfig['cartella'] . "/IUSVE")) {
            rrmdir($consegnaConfig['cartella'] . "/IUSVE");
        }
        $crawler = $client->request('GET', '/checkPassword?username=' . $username . "&password=" . $password);
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals($client->getResponse()->getContent(), '{"stato":false,"firstname":"Utente","lastname":"Test","classe":"IUSVE","insegnanti":[]}');
        $dirConsegna = $consegnaConfig['cartella'] . "/IUSVE/insegnante 1";
        if (file_exists($consegnaConfig['cartella'] . "/IUSVE")) {
            rrmdir($consegnaConfig['cartella'] . "/IUSVE");
        }
        mkdir($consegnaConfig['cartella'] . "/IUSVE");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 1");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 2");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 3");
        $crawler = $client->request('GET', '/checkPassword?username=' . $username . "&password=" . $password);
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals($client->getResponse()->getContent(), '{"stato":true,"firstname":"Utente","lastname":"Test","classe":"IUSVE","insegnanti":[{"value":"insegnante 2"},{"value":"insegnante 3"},{"value":"insegnante 1"}]}');

        rrmdir($consegnaConfig['cartella'] . "/IUSVE");
    }

    public function testListaCompitiAction() {
        $client = static::createClient();
        $username = "";
        $password = "mypassword";
        $username = "utente.test";
        $password = "mypassword";
        $consegnaConfig = $client->getContainer()->getParameter('consegna_elaborati');
        $dirConsegna = $consegnaConfig['cartella'] . "/IUSVE";
        $dirConsegna = $consegnaConfig['cartella'] . "/IUSVE/insegnante 1";
        if (file_exists($consegnaConfig['cartella'] . "/IUSVE")) {
            rrmdir($consegnaConfig['cartella'] . "/IUSVE");
        }
        mkdir($consegnaConfig['cartella'] . "/IUSVE");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 1");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 1/compito 1");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 1/[chiuso]compito 2");
        mkdir($consegnaConfig['cartella'] . "/IUSVE/insegnante 1/compito 3");

        $crawler = $client->request('GET', '/checkPassword?username=' . $username . "&password=" . $password);
        $crawler = $client->request('GET', '/listaCompiti?insegnante=insegnante 1');
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals($client->getResponse()->getContent(), '{"compiti":["compito 1","compito 3"]}');
        rrmdir($consegnaConfig['cartella'] . "/IUSVE");
    }

}

function rrmdir($dir) {
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}

<?php
namespace Consegna\ElaboratiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Consegna\ElaboratiBundle\Studente;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction() {
                return $this->render('ConsegnaElaboratiBundle:Default:index.html.twig');
    }

    /**
     * @Route("/consegna", name="consegna")
     * @Template
     */
    public function consegnaAction() {
        return $this->render('ConsegnaElaboratiBundle:Default:consegna.html.twig');
    }

    /**
     * @Route("/checkPassword", name="checkPassword")
     * @Template
     */
    public function checkPasswordAction() {
        $request = $this->get('Request');
        $username = $request->get('username',false);
        $password = $request->get('password',false);
        if ((!$username) or (!$password)) {
$response = new Response(json_encode(array('stato'=>false)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;            
        }
        
        $adConfig = $this->container->getParameter('ad_ldap');
        
        $studente = new Studente($adConfig);
        $insegnanti = false;
        $stato = false;
        if ($studente->checkPassword($username,$password)==true) {
            $stato=false;
            
                    if (($studente->getFirstname()) and ($studente->getClasse())) {
                    $consegnaConfig = $this->container->getParameter('consegna_elaborati');
                    $dirConsegna = $consegnaConfig['cartella'] . "/" . $studente->getClasse();
                    if (!file_exists($dirConsegna)) {
                        mkdir($dirConsegna);
                    }
                    $d = dir($dirConsegna);
                    $insegnanti = array();
                    while (false !== ($entry = $d->read())) {
                        if (substr($entry, 0, 1) == '.') {
                            continue;
                        }
                        $d2 = dir($dirConsegna . "/" . $entry);
                        $haSottoCartelle = false;
                        while (false !== ($entry2 = $d2->read())) {
                            if (substr($entry, 0, 1) == '.') {
                                continue;
                            }
                            $haSottoCartelle = true;
                        }
                        $d2->close();
                        if ($haSottoCartelle) {
                            $stato = true;
                            $insegnanti[] = array("value" => $entry);
                        }
                    }
                    $d->close();
                }
                $elements=array('stato' => $stato, 
                                     'firstname' => $studente->getFirstname(),
                                    'lastname' => $studente->getLastname(),
                                    "classe" => $studente->getClasse(),
                                    "insegnanti" => $insegnanti);
                $response = new Response(json_encode($elements));
                $session = $this->getRequest()->getSession();
                foreach ($elements as $key => $value) {
                 $session->set($key,$value);
                }
                
            } else {
        $response = new Response(json_encode(array('stato'=>false)));
            }
        
    
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/listaCompiti", name="listaCompiti")
     * @Template
     */
    public function listaCompitiAction() {
        $request = $this->get('request');
        $session = $this->getRequest()->getSession(); 
        $username = $session->get('username');
        $classe = $session->get('classe');
        $insegnante = $request->get('insegnante');
        $session->set('insegnante',$insegnante);
        $consegnaConfig = $this->container->getParameter('consegna_elaborati');
        $dirConsegna = $consegnaConfig['cartella'];
        $dirConsegna = $dirConsegna . "/" . $classe . "/" . $insegnante;
        if (!file_exists($dirConsegna)) {
            mkdir($dirConsegna);
        }
        $d = dir($dirConsegna);
        $compiti = array();
        while (false !== ($entry = $d->read())) {
            if (substr($entry, 0, 1) == '.') {
                continue;
            }
            if (substr($entry, 0, 8) == '[chiuso]') {
                continue;
            }
            $compiti[] = $entry;
        }
        $d->close();
        
        $response = new Response(json_encode(array('compiti' => $compiti)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/upload", name="upload")
     * @Template
     */
    public function uploadAction() {
        $request = $this->get('request');
        $session = $this->getRequest()->getSession(); 
        
            
                $consegnaConfig = $this->container->getParameter('consegna_elaborati');
            
            
        $fileBag = $this->get('request')->files->all();
                $dirConsegna = $consegnaConfig['cartella'] . "/" .
                            $session->get('classe') . "/" .
                            $session->get('insegnante') . "/" .
                            $request->get('hidden_compito'). "/" .
                            $session->get('lastname') .' '.$session->get('firstname'). " " .date('Y-m-d H-i-s');
        if (!file_exists($dirConsegna)) {
            mkdir($dirConsegna);
        }
                        
$filesName=array();
        foreach ($fileBag['files'] as $uploadedFile) {
            //$file->move(dove metto il file));
        $filesName[]=$uploadedFile->getClientOriginalName();
                    $uploadedFile->move($dirConsegna,$uploadedFile->getClientOriginalName());
        }
        return $this->render('ConsegnaElaboratiBundle:Default:upload.html.twig', 
                array(
                           'files' => $filesName,
                    'compito'=>$request->get('hidden_compito'),
                    'insegnante'=>$session->get('insegnante') 
                ));
    }

}

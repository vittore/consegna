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
use adLDAP;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction() {
        return $this->render('ConsegnaElaboratiBundle:Default:index.html.twig', array());
    }

    /**
     * @Route("/consegna", name="consegna")
     * @Template
     */
    public function consegnaAction() {
        $form = $this->get('form.factory')
                ->createBuilder('form')
                ->add('username', 'hidden')
                ->add('classe', 'hidden')
                ->add('insegnante', 'hidden')
                ->add('compito', 'hidden')
                ->add("dataFile", "file", array("required" => true))
                ->getForm();
        return $this->render('ConsegnaElaboratiBundle:Default:consegna.html.twig', array("form" => $form->createView()));
    }

    /**
     * @Route("/checkPassword", name="checkPassword")
     * @Template
     */
    public function checkPasswordAction() {
        $request = $this->get('Request');
        $username = $request->get('username');
        $password = $request->get('password');
        $return = false;
        $classe = false;
        $insegnanti = false;
        $stato = false;
        if (($username) and ($password)) {
            $adConfig = $this->container->getParameter('ad_ldap');
            $adConfig['account_suffix'] = '@' . $adConfig['account_suffix'];
            $ldap = new adLDAP($adConfig);
            $ldap->connect();
            $return = $ldap->authenticate($username, $password);
        }
        if ($return) {

            $memberOf = false;
            while ($memberOf == false) {
                $info = $ldap->user_info($username);
                if (array_key_exists(0, $info)) {
                    if (array_key_exists('memberof', $info[0])) {
                        $memberOf = $info[0]['memberof'];
                    }
                }
            }
            foreach ($memberOf as $cn) {
                if ((substr($cn, 0, 4) == "CN=_") and
                        (substr($cn, 7, 1) == ",")) {
                    $classe = substr($cn, 4, 3);
                    break;
                }
            }
            if ($classe) {
                $consegnaConfig = $this->container->getParameter('consegna_elaborati');
                $dirConsegna = $consegnaConfig['cartella'] . "/" . $classe;
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
        }
        $response = new Response(json_encode(array('stato' => $stato, "classe" => $classe, "insegnanti" => $insegnanti)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/listaCompiti", name="listaCompiti")
     * @Template
     */
    public function listaCompitiAction() {
        $request = $this->get('Request');
        $username = $request->get('username');
        $classe = $request->get('classe');
        $insegnante = $request->get('insegnante');
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
            $d2 = dir($dirConsegna . "/" . $entry);
            $consegnato = false;
            while (false !== ($entry2 = $d2->read())) {
                if (substr($entry, 0, 1) == '.') {
                    continue;
                }
                $nomeUtente = substr($entry2, 0, strrpos($entry2, '.'));
                if ($nomeUtente == $username) {
                    $consegnato = true;
                }
            }
            $d2->close();
            $compiti[] = array("value" => $entry, "consegnato" => $consegnato);
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
        $form = $this->get('form.factory')
                ->createBuilder('form')
                ->add('username', 'hidden')
                ->add('classe', 'hidden')
                ->add('insegnante', 'hidden')
                ->add('compito', 'hidden')
                ->add("dataFile", "file", array("required" => true))
                ->getForm();
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $consegnaConfig = $this->container->getParameter('consegna_elaborati');
                $estensioniPermesse = $consegnaConfig['estensioni_permesse'];
                $files = $request->files->get($form->getName());
                $data = $form->getData();
                $uploadedFile = $files["dataFile"]; //"dataFile" is the name on the field
                $ext = strtolower(substr($uploadedFile->getClientOriginalName(), strrpos($uploadedFile->getClientOriginalName(), '.') + 1));
                if (in_array($ext, $estensioniPermesse)) {
                    $dirConsegna = $consegnaConfig['cartella'] . "/" .
                            $data['classe'] . "/" .
                            $data['insegnante'] . "/" .
                            $data['compito'];
                    $uploadedFile->move($dirConsegna, $data['username'] . '.' . $ext);
                    $msg = 'File consegnato con successo!';
                    $errore = false;
                } else {
                    $msg = 'Tipo di file non permesso! Il file non è stato caricato.';
                    $errore = true;
                }
            } else {
                $msg = 'File non caricato!';
                $errore = true;
            }
        }
        return $this->render('ConsegnaElaboratiBundle:Default:upload.html.twig', array(
                    'errore' => $errore,
                    'messaggio' => $msg,
                    'estensioni' => $estensioniPermesse
                ));
    }

}

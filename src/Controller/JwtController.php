<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use ZF\OAuth2\Doctrine\Entity;
use Zend\Console\Adapter\Posix;
use Doctrine\ORM\EntityManager;

class JwtController extends AbstractActionController
{
    protected $config;
    protected $console;
    protected $objectManager;

    public function __construct(Array $config, Posix $console, EntityManager $objectManager)
    {
        $this->config = $config;
        $this->console = $console;
        $this->objectManager = $objectManager;
    }

    public function createAction()
    {
        $config = $this->config['zf-oauth2-doctrine']['default'];
        $console = $this->console;
        $objectManager = $this->objectManager;

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $client = $objectManager->getRepository(
            $config['mapping']['Client']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (!$client) {
            $console->write("Client not found", Color::RED);
            return;
        }

        // Get the subject
        $subject = Prompt\Line::prompt("The subject, usually a user_id.  Not required: ", true, 255);

        // Get public key path
        $publicKeyPath= '';
        while (!file_exists($publicKeyPath)) {
            $publicKeyPath = Prompt\Line::prompt("Public key path: ", false, 255);
        }
        $publicKey = file_get_contents($publicKeyPath);

        $jwt = new Entity\Jwt;
        $jwt->setClient($client);
        $jwt->setSubject($subject);
        $jwt->setPublicKey($publicKey);

        $objectManager->persist($jwt);
        $objectManager->flush();

        $console->write("JWT created\n", Color::GREEN);
    }

    public function listAction()
    {
        $config = $this->config['zf-oauth2-doctrine']['default'];
        $console = $this->console;
        $objectManager = $this->objectManager;

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $jwts = $objectManager->getRepository(
            $config['mapping']['Jwt']['entity']
        )->findBy(array(), array('id' => 'ASC'));

        $console->write("id\tclient\tclientId\tsubject\n", Color::YELLOW);
        foreach ($jwts as $jwt) {
            $console->write(
                  $jwt->getId()
                . "\t"
                . $jwt->getClient()->getId()
                . "\t"
                . $jwt->getClient()->getClientId()
                . "\t"
                . $jwt->getSubject()
                . "\n"
                , Color::CYAN);
        }
    }

    public function deleteAction()
    {
        $config = $this->config['zf-oauth2-doctrine']['default'];
        $console = $this->console;
        $objectManager = $this->objectManager;

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $jwt = $objectManager->getRepository(
            $config['mapping']['Jwt']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (!$jwt) {
            $console->write("JWT not found\n", Color::RED);
            return;
        }

        $objectManager->remove($jwt);
        $objectManager->flush();

        $console->write("JWT deleted\n", Color::GREEN);
    }
}

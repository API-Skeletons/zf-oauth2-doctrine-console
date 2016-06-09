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

class PublicKeyController extends AbstractActionController
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
            $console->writeLine("Client not found", Color::RED);
            return;
        }

        // Get public key path
        $publicKeyPath= '';
        while (!file_exists($publicKeyPath)) {
            $publicKeyPath = Prompt\Line::prompt("Public key path: ", false, 255);
        }
        $publicKey = file_get_contents($publicKeyPath);

        // Get private key path
        $privateKeyPath= '';
        while (!file_exists($privateKeyPath)) {
            $privateKeyPath = Prompt\Line::prompt("Private key path: ", false, 255);
        }
        $privateKey = file_get_contents($privateKeyPath);

        $options = array(
            0 => 'HS256',
            1 => 'HS384',
            2 => 'HS512',
            3 => 'RS256',
            4 => 'RS384',
            5 => 'RS512',
        );
        $encryptionAlgorithm = Prompt\Select::prompt("Encryption Algorithm: ", $options, false, false);

        $publicKeyEntity = new Entity\PublicKey;
        $publicKeyEntity->setClient($client);
        $publicKeyEntity->setPublicKey($publicKey);
        $publicKeyEntity->setPrivateKey($privateKey);
        $publicKeyEntity->setEncryptionAlgorithm($options[$encryptionAlgorithm]);

        $objectManager->persist($publicKeyEntity);
        $objectManager->flush();

        $console->write("Public key created\n", Color::GREEN);
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

        $client = $objectManager->getRepository(
            $config['mapping']['Client']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (!$client) {
            $console->write("Client not found\n", Color::RED);
            return;
        }

        if ($client->getPublicKey()) {
            $objectManager->remove($client->getPublicKey());
            $objectManager->flush();
            $console->write("Public key deleted\n", Color::GREEN);
        } else {
            $console->write("Public key does not exist for client\n", Color::YELLOW);
        }
    }
}

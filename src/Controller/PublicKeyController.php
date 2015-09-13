<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use ZF\OAuth2\Doctrine\Entity;

class PublicKeyController extends AbstractActionController
{
    public function createAction()
    {
        $applicationConfig = $this->getServiceLocator()->get('config');
        $config = $applicationConfig['zf-oauth2-doctrine']['default'];
        $console = $this->getServiceLocator()->get('console');
        $objectManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        if (!$client) {
            $console->write("Client not found", Color::RED);
            return;
        }

        $client = $objectManager->getRepository(
            $config['mapping']['ZF\OAuth2\Doctrine\Mapper\Client']['entity']
        )->find($this->getRequest()->getParam('id'));

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
        $applicationConfig = $this->getServiceLocator()->get('config');
        $config = $applicationConfig['zf-oauth2-doctrine']['default'];
        $console = $this->getServiceLocator()->get('console');
        $objectManager = $this->getServiceLocator()->get($config['object_manager']);

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $client = $objectManager->getRepository(
            $config['mapping']['ZF\OAuth2\Doctrine\Mapper\Client']['entity']
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

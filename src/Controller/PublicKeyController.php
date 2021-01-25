<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use RuntimeException;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Console\ColorInterface as Color;
use Laminas\Console\Prompt;
use ZF\OAuth2\Doctrine\Entity;

class PublicKeyController extends AbstractConsoleController
{
    public function createAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->getConfig()[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (! $this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['Client']['entity'])
            ->find($this->getRequest()->getParam('id'));

        if (! $client) {
            $this->getConsole()->writeLine("Client not found", Color::RED);
            return;
        }

        // Get public key path
        $publicKeyPath= '';
        while (! file_exists($publicKeyPath)) {
            $publicKeyPath = Prompt\Line::prompt("Public key path: ", false, 255);
        }
        $publicKey = file_get_contents($publicKeyPath);

        // Get private key path
        $privateKeyPath= '';
        while (! file_exists($privateKeyPath)) {
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
        $publicKeyEntity
            ->setClient($client)
            ->setPublicKey($publicKey)
            ->setPrivateKey($privateKey)
            ->setEncryptionAlgorithm($options[$encryptionAlgorithm])
            ;

        $this->getObjectManager()->persist($publicKeyEntity);
        $this->getObjectManager()->flush();

        $this->getConsole()->writeLine("Public key created", Color::GREEN);
    }

    public function deleteAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->getConfig()[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (! $this->getRequest() instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['Client']['entity'])
            ->find($this->getRequest()->getParam('id'));

        if (! $client) {
            $this->getConsole()->writeLine("Client not found", Color::RED);
            return;
        }

        if ($client->getPublicKey()) {
            $this->getObjectManager()->remove($client->getPublicKey());
            $this->getObjectManager()->flush();
            $this->getConsole()->writeLine("Public key deleted", Color::GREEN);
        } else {
            $this->getConsole()->writeLine("Public key does not exist for client", Color::YELLOW);
        }
    }
}

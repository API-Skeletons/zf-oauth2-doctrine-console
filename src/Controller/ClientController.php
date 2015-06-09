<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use Zend\Crypt\Password\Bcrypt;
use Doctrine\Common\Collections\ArrayCollection;

class ClientController extends AbstractActionController
{
    public function createAction()
    {
        $applicationConfig = $this->getServiceLocator()->get('config');
        $config = $applicationConfig['zf-oauth2-doctrine']['storage_settings'];
        $console = $this->getServiceLocator()->get('console');
        $objectManager = $this->getServiceLocator()->get($config['object_manager']);

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $clientEntity = new $config['mapping']['ZF\OAuth2\Doctrine\Mapper\Client']['entity'];

        // Get the User
        while(true) {
            $userId = Prompt\Line::prompt("User ID.  Not required. ? for list: ", true, 255);
            if ($userId == '?') {
                $users = $objectManager->getRepository(
                    $config['mapping']['ZF\OAuth2\Doctrine\Mapper\User']['entity']
                )->findAll();

                foreach ($users as $user) {
                    $console->write($user->getId() . "\t" . $user->getEmail() . "\n", Color::CYAN);
                }

                continue;
            }

            if ($userId) {
                $user = $objectManager->getRepository(
                    $config['mapping']['ZF\OAuth2\Doctrine\Mapper\User']['entity']
                )->find($userId);

                if (!$user) {
                    $console->write("User ID $userId not found.\n", Color::RED);
                    continue;
                }

                $clientEntity->setUser($user);
            }

            break;
        }

        // Get the client id
        $clientId = '';
        while (!$clientId) {
            $clientId = Prompt\Line::prompt("Client ID: ", false, 255);
            $client = $objectManager->getRepository(
                $config['mapping']['ZF\OAuth2\Doctrine\Mapper\Client']['entity']
            )->findOneBy(array(
                'clientId' => $clientId,
            ));
            if ($client) {
                $console->write('Client ID ' . $clientId . ' already exists', Color::RED);
                $clientId = '';
            }
        }
        $clientEntity->setClientId($clientId);

        // Get the client secret
        $secret = '';
        $secretVerify = false;
        while ($secret !== $secretVerify) {
            $secretPrompt = new Prompt\Password("Secret: ");
            $secret = $secretPrompt->show();
            $secretPrompt = new Prompt\Password("Verify Secret: ");
            $secretVerify = $secretPrompt->show();

            if ($secret !== $secretVerify) {
                $console->write("Password verification does not match.  Please try again.\n", Color::YELLOW);
                continue;
            }

            $bcrypt = new Bcrypt();
            $bcrypt->setCost(14);
            $clientEntity->setSecret($bcrypt->create($secret));
        }

        // Get the Redirect URI
        $redirectUri = Prompt\Line::prompt("Redirect URI.  Not required: ", true, 255);
        $clientEntity->setRedirectUri($redirectUri);

        // Get Grant Type(s)
        $console->write("Default Grant Types\n", Color::YELLOW);
        $console->write("authorization_code\n", Color::CYAN);
        $console->write("access_token\n", Color::CYAN);
        $console->write("refresh_token\n", Color::CYAN);
        $console->write("urn:ietf:params:oauth:grant-type:jwt-bearer\n", Color::CYAN);

        $grantType = Prompt\Line::prompt("Grant Types, comma delimited.  Not required: ", true, 255);
        $clientEntity->setGrantType(explode(',', $grantType));

        // Add scope(s)
        $clientScopes = new ArrayCollection();
        while (true) {
            $scopes = $objectManager->getRepository(
                $config['mapping']['ZF\OAuth2\Doctrine\Mapper\Scope']['entity']
            )->findBy(array(), array('scope' => 'DESC'));

            foreach ($clientScopes as $scope) {
                $scopes->removeElement($scope);
            }

            $options = array();
            foreach ($clientScopes as $scope) {
                $options[$scope->getId()] = $scope->getScope();
            }

            if (!$options) {
                $console->write("No Scopes exist.\n", Color::RED);
                break;
            }

            $answer = Prompt\Select::prompt(
                'Select Scope(s): ',
                $options,
                false,
                false
            );

            if (!$answer) {
                foreach ($clientScopes as $scope) {
                    $scope->addClient($clientEntity);
                    $clientEntity->addScope($scope);
                }
                break;
            } else {
                foreach ($clientScopes as $scope) {
                    if ($scope->getId() == $answer) {
                        $clientScopes->add($scope);
                        continue;
                    }
                }
            }
        }

        $objectManager->persist($clientEntity);
        $objectManager->flush();

        $console->write("Client has been created\n", Color::GREEN);
    }
}

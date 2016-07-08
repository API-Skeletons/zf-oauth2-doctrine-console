<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class ClientController extends AbstractActionController
{
    protected $config;
    protected $console;
    protected $objectManager;

    public function __construct(Array $config, Console $console, EntityManager $objectManager)
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

        $clientEntity = new $config['mapping']['Client']['entity'];

        // Get the User
        while(true) {
            $userId = Prompt\Line::prompt("User ID.  Not required. ? for list: ", true, 255);
            if ($userId == '?') {
                $users = $objectManager->getRepository(
                    $config['mapping']['User']['entity']
                )->findAll();

                foreach ($users as $user) {
                    $console->write($user->getId() . "\t" . $user->getEmail() . "\n", Color::CYAN);
                }

                continue;
            }

            if ($userId) {
                $user = $objectManager->getRepository(
                    $config['mapping']['User']['entity']
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
                $config['mapping']['Client']['entity']
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
            // there is a problem with Prompt\Password in Windows consoles...
            $secretPrompt = new Prompt\Line("Secret: ");
            $secret = $secretPrompt->show();
            $secretPrompt = new Prompt\Line("Verify Secret: ");
            $secretVerify = $secretPrompt->show();

            if ($secret !== $secretVerify) {
                $console->write("Password verification does not match.  Please try again.\n", Color::YELLOW);
                continue;
            }

            $clientEntity->setSecret(
                password_hash(
                    $secret,
                    PASSWORD_BCRYPT,
                    ['cost' => $config['bcrypt_cost']]
                )
            );
        }

        // Get the Redirect URI
        $redirectUri = Prompt\Line::prompt("Redirect URI.  Not required: ", true, 255);
        $clientEntity->setRedirectUri($redirectUri);

        // Get Grant Type(s)
        $console->write("Default Grant Types\n", Color::YELLOW);
        $console->write("authorization_code\n", Color::CYAN);
        $console->write("client_credentials\n", Color::CYAN);
        $console->write("refresh_token\n", Color::CYAN);
        $console->write("implicit\n", Color::CYAN);
        $console->write("urn:ietf:params:oauth:grant-type:jwt-bearer\n", Color::CYAN);

        $grantType = Prompt\Line::prompt("Grant Types, comma delimited.  Not required: ", true, 255);
        $clientEntity->setGrantType(explode(',', $grantType));

        // Add scope(s)
        $clientScopes = new ArrayCollection();
        while (true) {
            $scopeArray = $objectManager->getRepository(
                $config['mapping']['Scope']['entity']
            )->findBy(array(), array('id' => 'ASC'));

            $scopes = new ArrayCollection();
            foreach ($scopeArray as $scope) {
                if (! $clientScopes->contains($scope)) {
                    $scopes->add($scope);
                }
            }

            $options = array(
                0 => 'Done Selecting Scopes',
            );
            foreach ($scopes as $scope) {
                $options[$scope->getId()] = $scope->getScope();
            }

            if (!$options) {
                $console->write("No Scopes exist.\n", Color::RED);
                break;
            }

            if (sizeof($clientScopes)) {
                $console->write("Selected Scopes\n", Color::YELLOW);

                foreach ($clientScopes as $scope) {
                    $console->write($scope->getScope() . "\n", Color::CYAN);
                }
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
                foreach ($scopes as $scope) {
                    if ($scope->getId() == $answer) {
                        $clientScopes->add($scope);
                        echo "$answer selected\n";
                        break;
                    }
                }
            }
        }

        $objectManager->persist($clientEntity);
        $objectManager->flush();

        $console->write("Client created\n", Color::GREEN);
    }

    public function updateAction()
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

        $clientEntity = $objectManager->getRepository(
            $config['mapping']['Client']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (!$clientEntity) {
            $console->write("Client not found", Color::RED);
            return;
        }

        // Get the User
        while(true) {
            if ($clientEntity->getUser()) {
                $console->write("Current Value: " . $clientEntity->getUser()->getId() . "\n", Color::CYAN);
            } else {
                $console->write("Current Value: none\n", Color::CYAN);
            }

            $userId = Prompt\Line::prompt("User ID.  Not required. ? for list: ", true, 255);
            if ($userId == '?') {
                $users = $objectManager->getRepository(
                    $config['mapping']['User']['entity']
                )->findAll();

                foreach ($users as $user) {
                    $console->write($user->getId() . "\t" . $user->getEmail() . "\n", Color::CYAN);
                }

                continue;
            }

            if ($userId) {
                $user = $objectManager->getRepository(
                    $config['mapping']['User']['entity']
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
            $console->write("Current Value: " . $clientEntity->getClientId() . "\n", Color::CYAN);
            $clientId = Prompt\Line::prompt("Client ID: ", false, 255);
            $client = $objectManager->getRepository(
                $config['mapping']['Client']['entity']
            )->findOneBy(array(
                'clientId' => $clientId,
            ));
            if ($client && ($client->getId() !== $clientEntity->getId())) {
                $console->write('Client ID ' . $clientId . ' already exists', Color::RED);
                $clientId = '';
            }
        }
        $clientEntity->setClientId($clientId);

        // Get the client secret
        $secret = '';
        $secretVerify = false;
        while ($secret !== $secretVerify) {
            $secretPrompt = new Prompt\Line("Secret: ");
            $secret = $secretPrompt->show();
            $secretPrompt = new Prompt\Line("Verify Secret: ");
            $secretVerify = $secretPrompt->show();

            if ($secret !== $secretVerify) {
                $console->write("Password verification does not match.  Please try again.\n", Color::YELLOW);
                continue;
            }

            $clientEntity->setSecret(
                password_hash(
                    $secret,
                    PASSWORD_BCRYPT,
                    ['cost' => $config['bcrypt_cost']]
                )
            );
        }

        // Get the Redirect URI
        $console->write("Current Value: " . $clientEntity->getRedirectUri() . "\n", Color::CYAN);
        $redirectUri = Prompt\Line::prompt("Redirect URI.  Not required: ", true, 255);
        $clientEntity->setRedirectUri($redirectUri);

        // Get Grant Type(s)
        $console->write("Current Value: " . implode(',', $clientEntity->getGrantType()) . "\n", Color::CYAN);

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
            if (sizeof($clientEntity->getScope())) {
                $console->write("Current Scope(s)\n", Color::YELLOW);
                foreach ($clientEntity->getScope() as $scope) {
                    $console->write($scope->getScope() . "\n", Color::CYAN);
                }
            }

            $scopeArray = $objectManager->getRepository(
                $config['mapping']['Scope']['entity']
            )->findBy(array(), array('id' => 'ASC'));

            $scopes = new ArrayCollection();
            foreach ($scopeArray as $scope) {
                if (! $clientScopes->contains($scope)) {
                    $scopes->add($scope);
                }
            }

            $options = array(
                0 => 'Done Selecting Scopes',
            );
            foreach ($scopes as $scope) {
                $options[$scope->getId()] = $scope->getScope();
            }

            if (!$options) {
                $console->write("No Scopes exist.\n", Color::RED);
                break;
            }

            if (sizeof($clientScopes)) {
                $console->write("Selected Scopes\n", Color::YELLOW);

                foreach ($clientScopes as $scope) {
                    $console->write($scope->getScope() . "\n", Color::CYAN);
                }
            }

            $answer = Prompt\Select::prompt(
                'Select Scope(s): ',
                $options,
                false,
                false
            );

            if (!$answer) {
                foreach ($clientEntity->getScope() as $scope) {
                    $scope->removeClient($clientEntity);
                    $clientEntity->removeScope($scope);
                }
                foreach ($clientScopes as $scope) {
                    $scope->addClient($clientEntity);
                    $clientEntity->addScope($scope);
                }
                break;
            } else {
                foreach ($scopes as $scope) {
                    if ($scope->getId() == $answer) {
                        $clientScopes->add($scope);
                        echo "$answer selected\n";
                        break;
                    }
                }
            }
        }

        $objectManager->flush();

        $console->write("Client updated\n", Color::GREEN);
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

        $clients = $objectManager->getRepository(
            $config['mapping']['Client']['entity']
        )->findBy(array(), array('id' => 'ASC'));

        $console->write("id\tclientId\tredirectUri\tgrantType\n", Color::YELLOW);
        foreach ($clients as $client) {
            $console->write(
                  $client->getId()
                . "\t"
                . $client->getClientId()
                . "\t"
                . $client->getRedirectUri()
                . "\t"
                . implode(',', $client->getGrantType())
                . "\n", Color::CYAN
            );
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

        $client = $objectManager->getRepository(
            $config['mapping']['Client']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (!$client) {
            $console->write("Client not found\n", Color::RED);
            return;
        }

        $objectManager->remove($client);
        $objectManager->flush();

        $console->write("Client deleted\n", Color::GREEN);
    }
}

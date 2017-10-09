<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use RuntimeException;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use Zend\Crypt\Password\Bcrypt;
use Doctrine\Common\Collections\ArrayCollection;

final class ClientController extends AbstractConsoleController
{
    public function createAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->getConfig()[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $clientEntity = new $config['mapping']['Client']['entity'];

        // Get the User
        while(true) {
            $userId = Prompt\Line::prompt("User ID.  Not required. ? for list: ", true, 255);
            if ($userId == '?') {
                $users = $this->getObjectManager()->getRepository(
                    $config['mapping']['User']['entity']
                )->findAll();

                foreach ($users as $user) {
                    $this->getConsole()->writeLine($user->getId() . "\t" . $user->getEmail(), Color::CYAN);
                }

                continue;
            }

            if ($userId) {
                $user = $this->getObjectManager()->getRepository(
                    $config['mapping']['User']['entity']
                )->find($userId);

                if (!$user) {
                    $this->getConsole()->write("User ID $userId not found.", Color::RED);
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
            $client = $this->getObjectManager()->getRepository(
                $config['mapping']['Client']['entity']
            )->findOneBy(array(
                'clientId' => $clientId,
            ));
            if ($client) {
                $this->getConsole()->writeLine('Client ID ' . $clientId . ' already exists', Color::RED);
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
                $this->getConsole()->writeLine("Password verification does not match.  Please try again.", Color::YELLOW);
                continue;
            }

            $bcrypt = new Bcrypt();
            $bcrypt->setCost($config['bcrypt_cost']);
            $clientEntity->setSecret($bcrypt->create($secret));
        }

        // Get the Redirect URI
        $redirectUri = Prompt\Line::prompt("Redirect URI.  Not required: ", true, 255);
        $clientEntity->setRedirectUri($redirectUri);

        // Get Grant Type(s)
        $this->getConsole()->writeLine("Default Grant Types", Color::YELLOW);
        $this->getConsole()->writeLine("authorization_code", Color::CYAN);
        $this->getConsole()->writeLine("client_credentials", Color::CYAN);
        $this->getConsole()->writeLine("refresh_token", Color::CYAN);
        $this->getConsole()->writeLine("implicit", Color::CYAN);
        $this->getConsole()->writeLine("urn:ietf:params:oauth:grant-type:jwt-bearer", Color::CYAN);

        $grantType = Prompt\Line::prompt("Grant Types, comma delimited.  Not required: ", true, 255);
        $clientEntity->setGrantType(explode(',', $grantType));

        // Add scope(s)
        $clientScopes = new ArrayCollection();
        while (true) {
            $scopeArray = $this->getObjectManager()->getRepository(
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

            if (! $options) {
                $this->getConsole()->writeLine("No Scopes exist.", Color::RED);
                break;
            }

            if (sizeof($clientScopes)) {
                $this->getConsole()->writeLine("Selected Scopes", Color::YELLOW);

                foreach ($clientScopes as $scope) {
                    $this->getConsole()->writeLine($scope->getScope(), Color::CYAN);
                }
            }

            $answer = Prompt\Select::prompt(
                'Select Scope(s): ',
                $options,
                false,
                false
            );

            if (! $answer) {
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

        $this->getObjectManager()->persist($clientEntity);
        $this->getObjectManager()->flush();

        $this->getConsole()->write("Client created\n", Color::GREEN);
    }

    public function updateAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->getConfig()[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $clientEntity = $this->getObjectManager()->getRepository(
            $config['mapping']['Client']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (! $clientEntity) {
            $this->getConsole()->writeLine("Client not found", Color::RED);

            return;
        }

        // Get the User
        while(true) {
            if ($clientEntity->getUser()) {
                $this->getConsole()->writeLine("Current Value: " . $clientEntity->getUser()->getId(), Color::CYAN);
            } else {
                $this->getConsole()->writeLine("Current Value: none", Color::CYAN);
            }

            $userId = Prompt\Line::prompt("User ID.  Not required. ? for list: ", true, 255);
            if ($userId == '?') {
                $users = $this->getObjectManager()->getRepository(
                    $config['mapping']['User']['entity']
                )->findAll();

                foreach ($users as $user) {
                    $this->getConsole()->writeLine($user->getId() . "\t" . $user->getEmail(), Color::CYAN);
                }

                continue;
            }

            if ($userId) {
                $user = $this->getObjectManager()
                    ->getRepository($config['mapping']['User']['entity'])
                    ->find($userId);

                if (! $user) {
                    $this->getConsole()->writeLine("User ID $userId not found.", Color::RED);

                    continue;
                }

                $clientEntity->setUser($user);
            }

            break;
        }

        // Get the client id
        $clientId = '';
        while (! $clientId) {
            $this->getConsole()->writeLine("Current Value: " . $clientEntity->getClientId(), Color::CYAN);
            $clientId = Prompt\Line::prompt("Client ID: ", false, 255);
            $client = $this->getObjectManager()
                ->getRepository($config['mapping']['Client']['entity'])
                ->findOneBy(array(
                    'clientId' => $clientId,
                ));

            if ($client && ($client->getId() !== $clientEntity->getId())) {
                $this->getConsole()->writeLine('Client ID ' . $clientId . ' already exists', Color::RED);
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
                $this->getConsole()->writeLine("Password verification does not match.  Please try again.", Color::YELLOW);
                continue;
            }

            $bcrypt = new Bcrypt();
            $bcrypt->setCost($config['bcrypt_cost']);
            $clientEntity->setSecret($bcrypt->create($secret));
        }

        // Get the Redirect URI
        $this->getConsole()->writeLine("Current Value: " . $clientEntity->getRedirectUri(), Color::CYAN);
        $redirectUri = Prompt\Line::prompt("Redirect URI.  Not required: ", true, 255);
        $clientEntity->setRedirectUri($redirectUri);

        // Get Grant Type(s)
        $this->getConsole()->writeLine("Current Value: " . implode(',', $clientEntity->getGrantType()), Color::CYAN);

        $this->getConsole()->writeLine("Default Grant Types", Color::YELLOW);
        $this->getConsole()->writeLine("authorization_code", Color::CYAN);
        $this->getConsole()->writeLine("access_token", Color::CYAN);
        $this->getConsole()->writeLine("refresh_token", Color::CYAN);
        $this->getConsole()->writeLine("urn:ietf:params:oauth:grant-type:jwt-bearer", Color::CYAN);

        $grantType = Prompt\Line::prompt("Grant Types, comma delimited.  Not required: ", true, 255);
        $clientEntity->setGrantType(explode(',', $grantType));

        // Add scope(s)
        $clientScopes = new ArrayCollection();
        while (true) {
            if (sizeof($clientEntity->getScope())) {
                $this->getConsole()->writeLine("Current Scope(s)", Color::YELLOW);

                foreach ($clientEntity->getScope() as $scope) {
                    $this->getConsole()->writeLine($scope->getScope(), Color::CYAN);
                }
            }

            $scopeArray = $this->getObjectManager()
                ->getRepository($config['mapping']['Scope']['entity'])
                ->findBy(array(), array('id' => 'ASC'));

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
                $this->getConsole()->writeLine("No Scopes exist.", Color::RED);
                break;
            }

            if (sizeof($clientScopes)) {
                $this->getConsole()->writeLine("Selected Scopes", Color::YELLOW);

                foreach ($clientScopes as $scope) {
                    $this->getConsole()->writeLine($scope->getScope(), Color::CYAN);
                }
            }

            $answer = Prompt\Select::prompt(
                'Select Scope(s): ',
                $options,
                false,
                false
            );

            if (! $answer) {
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

        $this->getObjectManager()->flush();

        $this->getConsole()->writeLine("Client updated", Color::GREEN);
    }

    public function listAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->getConfig()[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $clients = $this->getObjectManager()
            ->getRepository($config['mapping']['Client']['entity'])
            ->findBy(array(), array('id' => 'ASC'));

        $this->getConsole()->writeLine("id\tclientId\tredirectUri\tgrantType", Color::YELLOW);
        foreach ($clients as $client) {
            $this->getConsole()->writeLine(
                  $client->getId()
                . "\t"
                . $client->getClientId()
                . "\t"
                . $client->getRedirectUri()
                . "\t"
                . implode(',', $client->getGrantType())
                , Color::CYAN
            );
        }
    }

    public function deleteAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->getConfig()[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $client = $this->getObjectManager()
            ->getRepository($config['mapping']['Client']['entity'])
            ->find($this->getRequest()->getParam('id'));

        if (! $client) {
            $this->getConsole()->writeLine("Client not found", Color::RED);

            return;
        }

        $this->getObjectManager()->remove($client);
        $this->getObjectManager()->flush();

        $this->getConsole()->writeLine("Client deleted", Color::GREEN);
    }
}

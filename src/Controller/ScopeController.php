<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;

class ScopeController extends AbstractActionController
{
    public function createAction()
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

        $scopeEntity = new $config['mapping']['Scope']['entity'];

        // Get the Scope
        $scope = Prompt\Line::prompt("Scope: ", false);
        $scopeEntity->setScope($scope);

        $default = Prompt\Confirm::prompt('Is this a default scope? [y/n] ', 'y', 'n');
        $scopeEntity->setIsDefault($default == 'y');

        $objectManager->persist($scopeEntity);
        $objectManager->flush();

        $console->write("Scope created\n", Color::GREEN);
    }

    public function updateAction()
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

        $scopeEntity = $objectManager->getRepository(
            $config['mapping']['Scope']['entity']
        )->find($this->getRequest()->getParam('id'));

        // Get the Scope
        $console->write("Current Value: " . $scopeEntity->getScope() . "\n", Color::CYAN);
        $scope = Prompt\Line::prompt("Scope: ", false);
        $scopeEntity->setScope($scope);

        $currentDefault = ($scopeEntity->getIsDefault()) ? 'Y': 'N';
        $console->write("Current Value: " . $currentDefault . "\n", Color::CYAN);
        $default = Prompt\Confirm::prompt('Is this a default scope? [y/n] ', 'y', 'n');
        $scopeEntity->setIsDefault($default == 'y');

        $objectManager->flush();

        $console->write("Scope updated\n", Color::GREEN);
    }

    public function listAction()
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

        $scopes = $objectManager->getRepository(
            $config['mapping']['Scope']['entity']
        )->findBy(array(), array('id' => 'ASC'));

        $console->write("id\tdefault\tscope\n", Color::YELLOW);
        foreach ($scopes as $scope) {
            $default = ($scope->getIsDefault()) ? 'Y': 'N';
            $console->write($scope->getId() . "\t" . $default . "\t" . $scope->getScope() . "\n", Color::CYAN);
        }
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

        $scope = $objectManager->getRepository(
            $config['mapping']['Scope']['entity']
        )->find($this->getRequest()->getParam('id'));

        if (!$scope) {
            $console->write("Scope not found\n", Color::RED);
            return;
        }

        $objectManager->remove($scope);
        $objectManager->flush();

        $console->write("Scope deleted\n", Color::GREEN);
    }
}

<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use RuntimeException;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;

class ScopeController extends AbstractConsoleController implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    private $config;

    public function __construct(ObjectManager $objectManager, Console $console, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->setConsole($console);
        $this->config = $config;
    }

    public function createAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $scopeEntity = new $config['mapping']['Scope']['entity'];

        // Get the Scope
        $scope = Prompt\Line::prompt("Scope: ", false);
        $scopeEntity->setScope($scope);

        $default = Prompt\Confirm::prompt('Is this a default scope? [y/n] ', 'y', 'n');
        $scopeEntity->setIsDefault($default == 'y');

        $this->getObjectManager()->persist($scopeEntity);
        $this->getObjectManager()->flush();

        $this->getConsole()->writeLine("Scope created", Color::GREEN);
    }

    public function updateAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $scopeEntity = $this->getObjectManager()
            ->getRepository($config['mapping']['Scope']['entity'])
            ->find($this->getRequest()->getParam('id'));

        // Get the Scope
        $this->getConsole()->writeLine("Current Value: " . $scopeEntity->getScope(), Color::CYAN);
        $scope = Prompt\Line::prompt("Scope: ", false);
        $scopeEntity->setScope($scope);

        $currentDefault = ($scopeEntity->getIsDefault()) ? 'Y': 'N';
        $this->getConsole()->writeLine("Current Value: " . $currentDefault, Color::CYAN);
        $default = Prompt\Confirm::prompt('Is this a default scope? [y/n] ', 'y', 'n');
        $scopeEntity->setIsDefault($default == 'y');

        $this->getObjectManager()->flush();

        $this->getConsole()->write("Scope updated\n", Color::GREEN);
    }

    public function listAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $scopes = $this->getObjectManager()
            ->getRepository($config['mapping']['Scope']['entity'])
            ->findBy(array(), array('id' => 'ASC'));

        $this->getConsole()->writeLine("id\tdefault\tscope", Color::YELLOW);
        foreach ($scopes as $scope) {
            $default = ($scope->getIsDefault()) ? 'Y': 'N';
            $this->getConsole()->writeLine(
                $scope->getId()
                . "\t"
                . $default
                . "\t"
                . $scope->getScope(),
                Color::CYAN
            );
        }
    }

    public function deleteAction()
    {
        $configSection = ($this->params()->fromRoute('config')) ?: 'default';
        $config = $this->config[$configSection];

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $scope = $this->getObjectManager()
            ->getRepository($config['mapping']['Scope']['entity'])
            ->find($this->getRequest()->getParam('id'));

        if (! $scope) {
            $this->getConsole()->writeLine("Scope not found", Color::RED);

            return;
        }

        $this->getObjectManager()->remove($scope);
        $this->getObjectManager()->flush();

        $this->getConsole()->writeLine("Scope deleted", Color::GREEN);
    }
}

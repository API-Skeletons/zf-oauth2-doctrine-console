<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Laminas\Mvc\Console\Controller\AbstractConsoleController as ZendAbstractConsoleController;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Persistence\ProvidesObjectManager;

abstract class AbstractConsoleController extends ZendAbstractConsoleController implements
    ObjectManagerAwareInterface
{
    use ProvidesObjectManager;

    private $config;

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function __construct(ObjectManager $objectManager, Console $console, array $config)
    {
        $this->setObjectManager($objectManager);
        $this->setConsole($console);
        $this->setConfig($config);
    }
}
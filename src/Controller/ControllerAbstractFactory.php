<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

final class ControllerAbstractFactory implements AbstractFactoryInterface
{
    private $classes = [
        PublicKeyController::class,
        ClientController::class,
        JwtController::class,
        ScopeController::class,
    ];

    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return in_array($requestedName, $this->classes);
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this->canCreate($services, $requestedName);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $objectManager = $container->get('doctrine.entitymanager.orm_default');
        $console = $container->get('Console');
        $config = $container->get('Config')['zf-oauth2-doctrine'];

        return new $requestedName($objectManager, $console, $config);
    }

    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        return $this($services, $requestedName);
    }
}
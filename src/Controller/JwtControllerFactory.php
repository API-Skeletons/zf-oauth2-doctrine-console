<?php

namespace ZF\OAuth2\Doctrine\Console\Controller;

use Interop\Container\ContainerInterface;

class JwtControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {

        $objectManager = $container->get('doctrine.entitymanager.orm_default');
        $console = $container->get('Console');
        $config = $container->get('Config')['zf-oauth2-doctrine'];

        return new $requestedName($objectManager, $console, $config);
    }
}

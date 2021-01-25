<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Doctrine\Console;

use ZF\OAuth2\Client\Service\OAuth2Service;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\Console\Adapter\AdapterInterface as Console;

/**
 * ZF2 module
 */
class Module implements
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    public function getConsoleUsage(Console $console)
    {
        return array(
            'oauth2:client:create [--config=]' => 'Create client',
            'oauth2:client:list [--config=]' => 'List clients',
            'oauth2:client:update --id=# [--config=]' => 'Update client',
            'oauth2:client:delete --id=# [--config=]' => 'Delete client',

            'oauth2:scope:create [--config=]' => 'Create scope',
            'oauth2:scope:list [--config=]' => 'List scopes',
            'oauth2:scope:update --id=# [--config=]' => 'Update scope',
            'oauth2:scope:delete --id=# [--config=]' => 'Delete scope',

            'oauth2:public-key:create --id=# [--config=]' => 'Create public key.  id is a client record.',
            'oauth2:public-key:delete --id=# [--config=]' => 'Delete public key.  id is a client record.',

            'oauth2:jwt:create --id=# [--config=]' => 'Create a JWT entry.  id is a client record.',
            'oauth2:jwt:list [--config=]' => 'List JWT entries',
            'oauth2:jwt:delete --id=# [--config=]' => 'Delete a JWT entry.  id is a jwt record.',
        );
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Laminas\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Provide default configuration.
     *
     * @param return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();

        return [
            'controllers' => $provider->getControllerDependencyConfig(),
            'console' => ['router' => $provider->getConsoleRouterConfig()],
        ];
    }
}

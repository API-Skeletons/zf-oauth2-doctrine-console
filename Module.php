<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\OAuth2\Doctrine\Console;

use ZF\OAuth2\Client\Service\OAuth2Service;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

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
            'oauth2:client:create' => 'Create client',
            'oauth2:client:list' => 'List clients',
            'oauth2:client:update --id=#' => 'Update client',
            'oauth2:client:delete --id=#' => 'Delete client',

            'oauth2:scope:create' => 'Create scope',
            'oauth2:scope:list' => 'List scopes',
            'oauth2:scope:update --id=#' => 'Update scope',
            'oauth2:scope:delete --id=#' => 'Delete scope',

            'oauth2:public-key:create --id=#' => 'Create public key.  id is a client record.',
            'oauth2:public-key:delete --id=#' => 'Delete public key.  id is a client record.',

            'oauth2:jwt:create --id=#' => 'Create a JWT entry.  id is a client record.',
            'oauth2:jwt:list' => 'List JWT entries',
            'oauth2:jwt:delete --id=#' => 'Delete a JWT entry.  id is a jwt record.',
        );
    }

    /**
     * Retrieve autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array('namespaces' => array(
            __NAMESPACE__ => __DIR__ . '/src/',
        )));
    }

    /**
     * Retrieve module configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}

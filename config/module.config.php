<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'ZF\OAuth2\Doctrine\Console\Controller\Client' =>
                'ZF\OAuth2\Doctrine\Console\Controller\ClientController',
            'ZF\OAuth2\Doctrine\Console\Controller\Scope' =>
                'ZF\OAuth2\Doctrine\Console\Controller\ScopeController',
            'ZF\OAuth2\Doctrine\Console\Controller\Jwt' =>
                'ZF\OAuth2\Doctrine\Console\Controller\JwtController',
            'ZF\OAuth2\Doctrine\Console\Controller\PublicKey' =>
                'ZF\OAuth2\Doctrine\Console\Controller\PublicKeyController',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'oauth2-client-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:create',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Client',
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-scope-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:create',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Scope',
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-scope-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:list',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Scope',
                            'action'     => 'list'
                        ),
                    ),
                ),
                'oauth2-scope-update' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:update --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Scope',
                            'action'     => 'update'
                        ),
                    ),
                ),
                'oauth2-scope-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:delete --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Scope',
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-jwt-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:create',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Jwt',
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-public-key-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:public-key:create',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\PublicKey',
                            'action'     => 'create'
                        ),
                    ),
                ),
            ),
        ),
    ),
);

<?php

namespace ZF\OAuth2\Doctrine\Console;

return array(
    'controllers' => array(
        'invokables' => array(
            'ZF\OAuth2\Doctrine\Console\Controller\PublicKey' =>
                'ZF\OAuth2\Doctrine\Console\Controller\PublicKeyController',
        ),
        'factories' => [
            Controller\ClientController::class =>
                Controller\ClientControllerFactory::class,
            Controller\JwtController::class =>
                Controller\JwtControllerFactory::class,
            Controller\ScopeController::class =>
                Controller\ScopeControllerFactory::class,
        ],
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'oauth2-client-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:create [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ClientController::class,
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-client-update' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:update --id= [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ClientController::class,
                            'action'     => 'update'
                        ),
                    ),
                ),
                'oauth2-client-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:delete --id= [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ClientController::class,
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-client-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:list [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ClientController::class,
                            'action'     => 'list'
                        ),
                    ),
                ),
                'oauth2-scope-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:create [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-scope-update' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:update --id= [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'update'
                        ),
                    ),
                ),
                'oauth2-scope-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:delete --id= [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-scope-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:list [--config=]',
                        'defaults' => array(
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'list'
                        ),
                    ),
                ),
                'oauth2-public-key-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:public-key:create --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\PublicKey',
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-public-key-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:public-key:delete --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\PublicKey',
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-jwt-create' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:create --id=:id [--config=]',
                        'defaults' => array(
                            'controller' => Controller\JwtController::class,
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-jwt-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:delete --id=:id [--config=]',
                        'defaults' => array(
                            'controller' => Controller\JwtController::class,
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-jwt-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:list [--config=]',
                        'defaults' => array(
                            'controller' => Controller\JwtController::class,
                            'action'     => 'list'
                        ),
                    ),
                ),
            ),
        ),
    ),
);

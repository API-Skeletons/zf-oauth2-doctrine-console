<?php

namespace ZF\OAuth2\Doctrine\Console;

class ConfigProvider
{
    /**
     * Return general purpose zf-oauth2-doctrine configuration
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'controllers' => $this->getControllerDependencyConfig(),
            'console' => [
                'route' => $this->getConsoleRouterConfig(),
            ],
        ];
    }

    public function getControllerDependencyConfig()
    {
        return [
            'abstract_factories' => [
                Controller\ControllerAbstractFactory::class,
            ]
        ];
    }

    public function getConsoleRouterConfig()
    {
        return [
            'routes' => [
                'oauth2-client-create' => [
                    'options' => [
                        'route'    => 'oauth2:client:create [--config=]',
                        'defaults' => [
                            'controller' => Controller\ClientController::class,
                            'action'     => 'create'
                        ],
                    ],
                ],
                'oauth2-client-update' => [
                    'options' => [
                        'route'    => 'oauth2:client:update --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\ClientController::class,
                            'action'     => 'update'
                        ],
                    ],
                ],
                'oauth2-client-delete' => [
                    'options' => [
                        'route'    => 'oauth2:client:delete --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\ClientController::class,
                            'action'     => 'delete'
                        ],
                    ],
                ],
                'oauth2-client-list' => [
                    'options' => [
                        'route'    => 'oauth2:client:list [--config=]',
                        'defaults' => [
                            'controller' => Controller\ClientController::class,
                            'action'     => 'list'
                        ],
                    ],
                ],
                'oauth2-scope-create' => [
                    'options' => [
                        'route'    => 'oauth2:scope:create [--config=]',
                        'defaults' => [
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'create'
                        ],
                    ],
                ],
                'oauth2-scope-update' => [
                    'options' => [
                        'route'    => 'oauth2:scope:update --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'update'
                        ],
                    ],
                ],
                'oauth2-scope-delete' => [
                    'options' => [
                        'route'    => 'oauth2:scope:delete --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'delete'
                        ],
                    ],
                ],
                'oauth2-scope-list' => [
                    'options' => [
                        'route'    => 'oauth2:scope:list [--config=]',
                        'defaults' => [
                            'controller' => Controller\ScopeController::class,
                            'action'     => 'list'
                        ],
                    ],
                ],
                'oauth2-public-key-create' => [
                    'options' => [
                        'route'    => 'oauth2:public-key:create --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\PublicKeyController::class,
                            'action'     => 'create'
                        ],
                    ],
                ],
                'oauth2-public-key-delete' => [
                    'options' => [
                        'route'    => 'oauth2:public-key:delete --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\PublicKeyController::class,
                            'action'     => 'delete'
                        ],
                    ],
                ],
                'oauth2-jwt-create' => [
                    'options' => [
                        'route'    => 'oauth2:jwt:create --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\JwtController::class,
                            'action'     => 'create'
                        ],
                    ],
                ],
                'oauth2-jwt-delete' => [
                    'options' => [
                        'route'    => 'oauth2:jwt:delete --id= [--config=]',
                        'defaults' => [
                            'controller' => Controller\JwtController::class,
                            'action'     => 'delete'
                        ],
                    ],
                ],
                'oauth2-jwt-list' => [
                    'options' => [
                        'route'    => 'oauth2:jwt:list [--config=]',
                        'defaults' => [
                            'controller' => Controller\JwtController::class,
                            'action'     => 'list'
                        ],
                    ],
                ],
            ],
        ];
    }
}
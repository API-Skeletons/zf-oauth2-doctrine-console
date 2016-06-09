<?php

return array(
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
                'oauth2-client-update' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:update --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Client',
                            'action'     => 'update'
                        ),
                    ),
                ),
                'oauth2-client-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:delete --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Client',
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-client-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:client:list',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Client',
                            'action'     => 'list'
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
                'oauth2-scope-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:scope:list',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Scope',
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
                        'route'    => 'oauth2:jwt:create --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Jwt',
                            'action'     => 'create'
                        ),
                    ),
                ),
                'oauth2-jwt-delete' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:delete --id=:id',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Jwt',
                            'action'     => 'delete'
                        ),
                    ),
                ),
                'oauth2-jwt-list' => array(
                    'options' => array(
                        'route'    => 'oauth2:jwt:list',
                        'defaults' => array(
                            'controller' => 'ZF\OAuth2\Doctrine\Console\Controller\Jwt',
                            'action'     => 'list'
                        ),
                    ),
                ),
            ),
        ),
    ),
);

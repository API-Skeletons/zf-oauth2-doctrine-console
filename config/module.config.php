<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'ZF\OAuth2\Doctrine\Console\Controller\Jwt' => 
                'ZF\OAuth2\Doctrine\Console\Controller\JwtController',
            'ZF\OAuth2\Doctrine\Console\Controller\PublicKey' => 
                'ZF\OAuth2\Doctrine\Console\Controller\PublicKeyController',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
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

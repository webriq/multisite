<?php

return array(
    'router' => array(
        'routes' => array(
            'Grid\MultisitePlatform\AutoLogin\ByDomain' => array(
                'type'      => 'Zend\Mvc\Router\Http\Segment',
                'options'   => array(
                    'route'     => '/app/:locale/auto-login-to/:domain',
                    'defaults'  => array(
                        'controller' => 'Grid\MultisitePlatform\Controller\AutoLogin',
                        'action'     => 'by-domain',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Grid\MultisitePlatform\Controller\AutoLogin' => 'Grid\MultisitePlatform\Controller\AutoLoginController',
        ),
    ),
    'factory' => array(
        'Grid\User\Model\Authentication\AdapterFactory' => array(
            'adapter'    => array(
                'autoLogin' => 'Grid\MultisitePlatform\Authentication\AutoLoginAdapter',
            ),
        ),
    ),
);

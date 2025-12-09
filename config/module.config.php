<?php

namespace ResumeCours;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'resume-cours' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/resume-cours',
                            'defaults' => [
                                '__NAMESPACE__' => 'ResumeCours\Controller',
                                'controller' => 'Index',
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'study-whis-api' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/study-whis-api',
                            'defaults' => [
                                '__NAMESPACE__' => 'ResumeCours\Controller',
                                'controller' => 'StudyWhisApi',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'ResumeCours\Controller\Index' => 'ResumeCours\Controller\IndexController',
            'ResumeCours\Controller\StudyWhisApi' => 'ResumeCours\Controller\StudyWhisApiController',
        ],
    ],
    'navigation' => [
        'AdminModule' => [
            [
                'label' => 'ResumeCours',
                'route' => 'admin/resume-cours',
                'resource' => 'ResumeCours\Controller\Index',
                'privilege' => 'index',
                'class' => 'resume-cours-nav',
            ],
        ],
    ],
];

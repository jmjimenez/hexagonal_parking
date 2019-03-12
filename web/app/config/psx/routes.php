<?php

return [
    # API
    [
        ['POST'],
        '/assignusertoparking',
        \Jmj\Parking\Infrastructure\Psx\Controllers\AssignUserToParking::class
    ],
    [
        ['POST'],
        '/assignadministratorrightstouserforparking',
        \Jmj\Parking\Infrastructure\Psx\Controllers\AssignAdministratorRightsToUserForParking::class
    ],
    [
        ['POST'],
        '/createparking',
        \Jmj\Parking\Infrastructure\Psx\Controllers\CreateParking::class
    ],
    [
        ['POST'],
        '/createparkingslot',
        \Jmj\Parking\Infrastructure\Psx\Controllers\CreateParkingSlot::class
    ],

    # tool controller
    [['ANY'], '/tool/discovery', \PSX\Framework\Controller\Tool\DiscoveryController::class],
    [['ANY'], '/tool/routing', \PSX\Framework\Controller\Tool\RoutingController::class],
    [['ANY'], '/tool/doc', \PSX\Framework\Controller\Tool\Documentation\IndexController::class],
    [['ANY'], '/tool/doc/:version/*path', \PSX\Framework\Controller\Tool\Documentation\DetailController::class],
    [['ANY'], '/tool/raml/:version/*path', \PSX\Framework\Controller\Generator\RamlController::class],
    [['ANY'], '/tool/swagger/:version/*path', \PSX\Framework\Controller\Generator\SwaggerController::class],
    [['ANY'], '/tool/openapi/:version/*path', \PSX\Framework\Controller\Generator\OpenAPIController::class],
];

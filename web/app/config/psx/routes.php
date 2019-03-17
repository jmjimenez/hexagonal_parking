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
        '/deassignuserfromparking',
        \Jmj\Parking\Infrastructure\Psx\Controllers\DeassignUserFromParking::class
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
    [
        ['POST'],
        '/deleteparking',
        \Jmj\Parking\Infrastructure\Psx\Controllers\DeleteParking::class
    ],
    [
        ['POST'],
        '/deleteparkingslot',
        \Jmj\Parking\Infrastructure\Psx\Controllers\DeleteParkingSlot::class
    ],
    [
        ['POST'],
        '/createuserforparking',
        \Jmj\Parking\Infrastructure\Psx\Controllers\CreateUserForParking::class
    ],
    [
        ['POST'],
        '/updateparkingslotinformation',
        \Jmj\Parking\Infrastructure\Psx\Controllers\UpdateParkingSlotInformation::class
    ],
    [
        ['POST'],
        '/updateparkingslotinformation',
        \Jmj\Parking\Infrastructure\Psx\Controllers\UpdateParkingSlotInformation::class
    ],
    [
        ['POST'],
        '/assignparkingslottouserforperiod',
        \Jmj\Parking\Infrastructure\Psx\Controllers\AssignParkingSlotToUserForPeriod::class
    ],
    [
        ['POST'],
        '/freeassignedparkingslotforuserandperiod',
        \Jmj\Parking\Infrastructure\Psx\Controllers\FreeAssignedParkingSlotForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/getparkinginformationforuserandperiod',
        \Jmj\Parking\Infrastructure\Psx\Controllers\GetParkingInformationForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/getparkingslotreservationsforperiod',
        \Jmj\Parking\Infrastructure\Psx\Controllers\GetParkingSlotReservationsForPeriod::class
    ],
    [
        ['POST'],
        '/getparkingreservationsfordate',
        \Jmj\Parking\Infrastructure\Psx\Controllers\GetParkingReservationsForDate::class
    ],
    [
        ['POST'],
        '/getuserinformation',
        \Jmj\Parking\Infrastructure\Psx\Controllers\GetUserInformation::class
    ],
    [
        ['POST'],
        '/removeassignmentfromparkingslotforuseranddate',
        \Jmj\Parking\Infrastructure\Psx\Controllers\RemoveAssignmentFromParkingSlotForUserAndDate::class
    ],
    [
        ['POST'],
        '/reserveparkingslotforuserandperiod',
        \Jmj\Parking\Infrastructure\Psx\Controllers\ReserveParkingSlotForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/requestresetuserpassword',
        \Jmj\Parking\Infrastructure\Psx\Controllers\RequestResetUserPassword::class
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

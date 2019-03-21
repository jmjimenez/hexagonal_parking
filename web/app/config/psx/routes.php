<?php

return [
    # API
    [
        ['POST'],
        '/assignusertoparking',
        \Jmj\Parking\Infrastructure\Psx\Controller\AssignUserToParking::class
    ],
    [
        ['POST'],
        '/deassignuserfromparking',
        \Jmj\Parking\Infrastructure\Psx\Controller\DeassignUserFromParking::class
    ],
    [
        ['POST'],
        '/assignadministratorrightstouserforparking',
        \Jmj\Parking\Infrastructure\Psx\Controller\AssignAdministratorRightsToUserForParking::class
    ],
    [
        ['POST'],
        '/createparking',
        \Jmj\Parking\Infrastructure\Psx\Controller\CreateParking::class
    ],
    [
        ['POST'],
        '/createparkingslot',
        \Jmj\Parking\Infrastructure\Psx\Controller\CreateParkingSlot::class
    ],
    [
        ['POST'],
        '/deleteparking',
        \Jmj\Parking\Infrastructure\Psx\Controller\DeleteParking::class
    ],
    [
        ['POST'],
        '/deleteparkingslot',
        \Jmj\Parking\Infrastructure\Psx\Controller\DeleteParkingSlot::class
    ],
    [
        ['POST'],
        '/createuserforparking',
        \Jmj\Parking\Infrastructure\Psx\Controller\CreateUserForParking::class
    ],
    [
        ['POST'],
        '/updateparkingslotinformation',
        \Jmj\Parking\Infrastructure\Psx\Controller\UpdateParkingSlotInformation::class
    ],
    [
        ['POST'],
        '/updateparkingslotinformation',
        \Jmj\Parking\Infrastructure\Psx\Controller\UpdateParkingSlotInformation::class
    ],
    [
        ['POST'],
        '/assignparkingslottouserforperiod',
        \Jmj\Parking\Infrastructure\Psx\Controller\AssignParkingSlotToUserForPeriod::class
    ],
    [
        ['POST'],
        '/freeassignedparkingslotforuserandperiod',
        \Jmj\Parking\Infrastructure\Psx\Controller\FreeAssignedParkingSlotForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/getparkinginformationforuserandperiod',
        \Jmj\Parking\Infrastructure\Psx\Controller\GetParkingInformationForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/getparkingslotreservationsforperiod',
        \Jmj\Parking\Infrastructure\Psx\Controller\GetParkingSlotReservationsForPeriod::class
    ],
    [
        ['POST'],
        '/getparkingreservationsfordate',
        \Jmj\Parking\Infrastructure\Psx\Controller\GetParkingReservationsForDate::class
    ],
    [
        ['POST'],
        '/getuserinformation',
        \Jmj\Parking\Infrastructure\Psx\Controller\GetUserInformation::class
    ],
    [
        ['POST'],
        '/removeassignmentfromparkingslotforuseranddate',
        \Jmj\Parking\Infrastructure\Psx\Controller\RemoveAssignmentFromParkingSlotForUserAndDate::class
    ],
    [
        ['POST'],
        '/reserveparkingslotforuserandperiod',
        \Jmj\Parking\Infrastructure\Psx\Controller\ReserveParkingSlotForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/requestresetuserpassword',
        \Jmj\Parking\Infrastructure\Psx\Controller\RequestResetUserPassword::class
    ],
    [
        ['POST'],
        '/resetuserpassword',
        \Jmj\Parking\Infrastructure\Psx\Controller\ResetUserPassword::class
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

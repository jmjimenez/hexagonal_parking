<?php

use Jmj\Parking\Infrastructure\Psx\Controller\AssignAdministratorRightsToUserForParking;
use Jmj\Parking\Infrastructure\Psx\Controller\AssignParkingSlotToUserForPeriod;
use Jmj\Parking\Infrastructure\Psx\Controller\AssignUserToParking;
use Jmj\Parking\Infrastructure\Psx\Controller\CreateParking;
use Jmj\Parking\Infrastructure\Psx\Controller\CreateParkingSlot;
use Jmj\Parking\Infrastructure\Psx\Controller\CreateUserForParking;
use Jmj\Parking\Infrastructure\Psx\Controller\DeassignUserFromParking;
use Jmj\Parking\Infrastructure\Psx\Controller\DeleteParking;
use Jmj\Parking\Infrastructure\Psx\Controller\DeleteParkingSlot;
use Jmj\Parking\Infrastructure\Psx\Controller\DeleteUser;
use Jmj\Parking\Infrastructure\Psx\Controller\FreeAssignedParkingSlotForUserAndPeriod;
use Jmj\Parking\Infrastructure\Psx\Controller\GetParkingInformationForUserAndPeriod;
use Jmj\Parking\Infrastructure\Psx\Controller\GetParkingReservationsForDate;
use Jmj\Parking\Infrastructure\Psx\Controller\GetParkingSlotReservationsForPeriod;
use Jmj\Parking\Infrastructure\Psx\Controller\GetUserInformation;
use Jmj\Parking\Infrastructure\Psx\Controller\RemoveAssignmentFromParkingSlotForUserAndDate;
use Jmj\Parking\Infrastructure\Psx\Controller\RequestResetUserPassword;
use Jmj\Parking\Infrastructure\Psx\Controller\ReserveParkingSlotForUserAndPeriod;
use Jmj\Parking\Infrastructure\Psx\Controller\ResetUserPassword;
use Jmj\Parking\Infrastructure\Psx\Controller\UpdateParkingSlotInformation;
use Jmj\Parking\Infrastructure\Psx\Controller\UserLogin;
use PSX\Framework\Controller\Generator\OpenAPIController;
use PSX\Framework\Controller\Generator\RamlController;
use PSX\Framework\Controller\Generator\SwaggerController;
use PSX\Framework\Controller\Tool\DiscoveryController;
use PSX\Framework\Controller\Tool\Documentation\DetailController;
use PSX\Framework\Controller\Tool\Documentation\IndexController;
use PSX\Framework\Controller\Tool\RoutingController;

return [
    # API
    [
        ['POST'],
        '/assignusertoparking',
        AssignUserToParking::class
    ],
    [
        ['POST'],
        '/deassignuserfromparking',
        DeassignUserFromParking::class
    ],
    [
        ['POST'],
        '/assignadministratorrightstouserforparking',
        AssignAdministratorRightsToUserForParking::class
    ],
    [
        ['POST'],
        '/createparking',
        CreateParking::class
    ],
    [
        ['POST'],
        '/createparkingslot',
        CreateParkingSlot::class
    ],
    [
        ['POST'],
        '/deleteparking',
        DeleteParking::class
    ],
    [
        ['POST'],
        '/deleteuser',
        DeleteUser::class
    ],
    [
        ['POST'],
        '/deleteparkingslot',
        DeleteParkingSlot::class
    ],
    [
        ['POST'],
        '/createuserforparking',
        CreateUserForParking::class
    ],
    [
        ['POST'],
        '/updateparkingslotinformation',
        UpdateParkingSlotInformation::class
    ],
    [
        ['POST'],
        '/updateparkingslotinformation',
        UpdateParkingSlotInformation::class
    ],
    [
        ['POST'],
        '/assignparkingslottouserforperiod',
        AssignParkingSlotToUserForPeriod::class
    ],
    [
        ['POST'],
        '/freeassignedparkingslotforuserandperiod',
        FreeAssignedParkingSlotForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/getparkinginformationforuserandperiod',
        GetParkingInformationForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/getparkingslotreservationsforperiod',
        GetParkingSlotReservationsForPeriod::class
    ],
    [
        ['POST'],
        '/getparkingreservationsfordate',
        GetParkingReservationsForDate::class
    ],
    [
        ['POST'],
        '/getuserinformation',
        GetUserInformation::class
    ],
    [
        ['POST'],
        '/removeassignmentfromparkingslotforuseranddate',
        RemoveAssignmentFromParkingSlotForUserAndDate::class
    ],
    [
        ['POST'],
        '/reserveparkingslotforuserandperiod',
        ReserveParkingSlotForUserAndPeriod::class
    ],
    [
        ['POST'],
        '/requestresetuserpassword',
        RequestResetUserPassword::class
    ],
    [
        ['POST'],
        '/resetuserpassword',
        ResetUserPassword::class
    ],
    [
        ['POST'],
        '/login',
        UserLogin::class
    ],

    # tool controller
    [['ANY'], '/tool/discovery', DiscoveryController::class],
    [['ANY'], '/tool/routing', RoutingController::class],
    [['ANY'], '/tool/doc', IndexController::class],
    [['ANY'], '/tool/doc/:version/*path', DetailController::class],
    [['ANY'], '/tool/raml/:version/*path', RamlController::class],
    [['ANY'], '/tool/swagger/:version/*path', SwaggerController::class],
    [['ANY'], '/tool/openapi/:version/*path', OpenAPIController::class],
];

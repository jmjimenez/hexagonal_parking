<?php

namespace Jmj\Parking\Application\Command\Handler\Exception;

//TODO: all parking exceptions should inherit from the same domain generic exception class
use Exception;

class ParkingNotFound extends Exception
{

}

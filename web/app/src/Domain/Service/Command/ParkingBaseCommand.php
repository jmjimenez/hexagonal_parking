<?php

namespace Jmj\Parking\Domain\Service\Command;

use Exception;
use Jmj\Parking\Common\Exception\InvalidDateRange;
use Jmj\Parking\Domain\Exception\ExceptionGeneratingUuid;
use Jmj\Parking\Domain\Exception\ParkingSlotDescriptionInvalid;
use Jmj\Parking\Domain\Exception\ParkingSlotNotFound;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberInvalid;
use Jmj\Parking\Domain\Exception\ParkingSlotReservationsForDateIncorrect;
use Jmj\Parking\Domain\Exception\UserEmailInvalid;
use Jmj\Parking\Domain\Exception\UserNameAlreadyExists;
use Jmj\Parking\Domain\Exception\NotAuthorizedOperation;
use Jmj\Parking\Domain\Exception\ParkingException;
use Jmj\Parking\Domain\Exception\ParkingSlotNumberAlreadyExists;
use Jmj\Parking\Domain\Exception\UserNotAssigned;
use Jmj\Parking\Domain\Exception\UserPasswordInvalid;

abstract class ParkingBaseCommand
{
    /**
     * @throws ExceptionGeneratingUuid
     * @throws InvalidDateRange
     * @throws NotAuthorizedOperation
     * @throws ParkingSlotNotFound
     * @throws ParkingSlotDescriptionInvalid
     * @throws ParkingSlotNumberAlreadyExists
     * @throws ParkingSlotNumberInvalid
     * @throws ParkingSlotReservationsForDateIncorrect
     * @throws UserEmailInvalid
     * @throws UserNameAlreadyExists
     * @throws UserNotAssigned
     * @throws UserPasswordInvalid
     */
    abstract protected function process();

    /**
     * @throws ParkingException
     */
    protected function processCatchingDomainEvents()
    {
        //TODO: review all exceptions thrown by all processes
        try {
            $this->process();
        } catch (NotAuthorizedOperation $e) {
            throw new ParkingException('User is not authorized to do this operation', 1, $e);
        } catch (UserNameAlreadyExists $e) {
            throw new ParkingException('User name already exists in this Parking', 2, $e);
        } catch (ParkingSlotNotFound $e) {
            throw new ParkingException('Parking Slot not found in this Parking', 3, $e);
        } catch (UserNotAssigned $e) {
            throw new ParkingException('User not assigned to this Parking', 4, $e);
        } catch (ParkingSlotNumberAlreadyExists $e) {
            throw new ParkingException('Parking Slot Number already exists in this Parking', 5, $e);
        } catch (ExceptionGeneratingUuid $e) {
            throw new ParkingException('Error generating valid Uuid', 6, $e);
        } catch (InvalidDateRange $e) {
            throw new ParkingException('Invalid date range', 7, $e);
        } catch (ParkingSlotDescriptionInvalid $e) {
            throw new ParkingException('Parking Slot description invalid', 8, $e);
        } catch (ParkingSlotNumberInvalid $e) {
            throw new ParkingException('Parking Slot number invalid', 9, $e);
        } catch (ParkingSlotReservationsForDateIncorrect $e) {
            throw new ParkingException('Parking Slot reservation for date is not correct', 10, $e);
        } catch (UserEmailInvalid $e) {
            throw new ParkingException('User email not valid', 11, $e);
        } catch (UserPasswordInvalid $e) {
            throw new ParkingException('User password not valid', 12, $e);
        } catch (Exception $e) {
            throw new ParkingException('Unknown exception', 99, $e);
        }
    }
}

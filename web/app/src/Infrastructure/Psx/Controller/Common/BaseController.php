<?php

namespace Jmj\Parking\Infrastructure\Psx\Controller\Common;

use Firebase\JWT\JWT;
use Jmj\Parking\Domain\Aggregate\User;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Filter\Oauth2Authentication;

class BaseController extends ControllerAbstract
{
    /**
     * @Inject
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @Inject("UserRepository")
     * @var \Jmj\Parking\Domain\Repository\User
     */
    protected $userRepository;

    /**
     * @Inject("ParkingRepository")
     * @var \Jmj\Parking\Domain\Repository\Parking
     */
    protected $parkingRepository;

    /**
     * @var User
     */
    protected $loggedInUser;

    public function getPreFilter()
    {
        $jwtConfig = $this->config->get('parking_jwt');

        $auth = new Oauth2Authentication(function ($accessToken) use ($jwtConfig) {
            $authInfo = JWT::decode($accessToken, $jwtConfig['secret'], [ $jwtConfig['algorithm'] ]);

            $this->loggedInUser = $this->userRepository->findByEmail($authInfo->email);

            if (!$this->loggedInUser instanceof User) {
                $this->loggedInUser = null;
                return false;
            }

            if (!$this->loggedInUser->checkPassword($authInfo->password)) {
                $this->loggedInUser = null;
                return false;
            }

            return true;
        });

        return [$auth];
    }
}

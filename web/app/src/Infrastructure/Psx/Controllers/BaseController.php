<?php

namespace Jmj\Parking\Infrastructure\Psx\Controllers;

use Firebase\JWT\JWT;
use PSX\Framework\Controller\ControllerAbstract;
use PSX\Framework\Filter\Oauth2Authentication;

class BaseController extends ControllerAbstract
{
    /**
     * @Inject
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    public function getPreFilter()
    {
        $jwtConfig = $this->config->get('parking_jwt');

        $auth = new Oauth2Authentication(function ($accessToken) use ($jwtConfig) {
            $user = JWT::decode($accessToken, $jwtConfig['secret'], $jwtConfig['algorithm']);
            if ($user->email == 'user01@test.com' && $user->password == 'user1password') {
                return true;
            }

            return false;
        });

        return [$auth];
    }
}

<?php

include '../app/vendor/autoload.php';

$container = new \Jmj\Parking\Infrastructure\Psx\Dependency\Container();
$container->setParameter('config.file', __DIR__ . '/../app/config/psx/configuration.php');

$engine      = new \PSX\Framework\Environment\WebServer\Engine();
$environment = new \PSX\Framework\Environment\Environment($container, $engine);

return $environment->serve();

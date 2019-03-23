<?php

namespace Jmj\Test\Unit\Infrastructure\Psx\Controller\InMemory\Pdo\Common;

use PSX\Framework\Bootstrap;
use PSX\Framework\Config\Config;

class TestBootstrap extends Bootstrap
{
    /**
     * {@inheritdoc}
     */
    public static function setupEnvironment(Config $config)
    {
        if (!defined('PSX')) {
            // define paths
            define('PSX_PATH_CACHE', $config->get('psx_path_cache'));
            define('PSX_PATH_PUBLIC', $config->get('psx_path_public'));
            define('PSX_PATH_SRC', $config->get('psx_path_src') ?: $config->get('psx_path_library'));

            /** @deprecated */
            define('PSX_PATH_LIBRARY', $config->get('psx_path_library'));

            // error handling
            if ($config['psx_debug'] === true) {
                $errorReporting = E_ALL | E_STRICT;
            } else {
                $errorReporting = 0;
            }

            error_reporting($errorReporting);
            set_error_handler('\PSX\Framework\Bootstrap::errorHandler');

            // annotation autoload
            $namespaces = $config->get('psx_annotation_autoload');
            if (!empty($namespaces) && is_array($namespaces)) {
                self::registerAnnotationLoader($namespaces);
            }

            // ini settings
            ini_set('date.timezone', $config['psx_timezone']);
            ini_set('docref_root', '');
            ini_set('html_errors', '0');

            // define in psx
            define('PSX', true);
        }
    }
}

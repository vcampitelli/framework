<?php
/**
 * Application path
 */
define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

/**
 * Application environment
 */
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'production'));

require __DIR__ . '/../../autoload.php';

try {
    // Reads app.ini file
    $config = new \Core\Config();
    $config->readIni(APPLICATION_PATH . '/core/config/app.ini');

    // Bootstrapping application
    $app = new \Core\Application($config);
    $app->run();
} catch (Exception $e) {
    if (APPLICATION_ENV == 'development') {
        var_dump($e);
    } else {
        die('Erro.');
    }
}

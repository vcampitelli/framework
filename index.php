<?php
/**
 * Application path
 */
define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

/**
 * Application environment
 */
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'production'));

/**
 * Dummy autoloader
 *
 * @author Vinícius Campitelli <eu@viniciuscampitelli.com>
 * @since  2016-10-30
 */
spl_autoload_register(function ($class) {
    $arrClass = \explode('\\', \str_replace('..', '', $class));
    
    // Main namespace
    $module = \strtolower(\array_shift($arrClass));
    
    $path = APPLICATION_PATH . "/{$module}/class/" . implode('/', $arrClass) . '.php';
    if (\is_file($path)) {
        include $path;
    }
});

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

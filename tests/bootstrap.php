<?php
/**
 * Bootstrap file for PHPUnit tests
 *
 * Autoloads the project classes
 */

// Define base path
define('BASE_PATH', dirname(dirname(__FILE__)));

// Load composer autoloader
$autoloader = require BASE_PATH . '/vendor/autoload.php';

// Ensure the test namespace is loaded
$autoloader->addPsr4('peeto\\idforward\\Tests\\', __DIR__);

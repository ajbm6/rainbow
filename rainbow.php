<?php

/**
 * Set the project base path.
 */
define('BASE_PATH', realpath(__DIR__));

/**
 * Set the system timezone.
 */
date_default_timezone_set('UTC');

/**
 * Include the auto-loader.
 */
require 'vendor/autoload.php';

/**
 * Imports.
 */
use Rainbow\Commands\GenerateCommand;
use Symfony\Component\Console\Application;

/**
 * Run application.
 */
$app = new Application;
$app->add(new GenerateCommand);
$app->run();

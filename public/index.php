<?php
/**
 * OpenCCK - CMS Backend entrypoint
 *
 * @package openCCK
 * @author Krupkin Sergey <rekryt@yandex.ru>
 */
define('PATH_ROOT', dirname(__DIR__));

// Initialize Framework
include '../bootstrap.php';

use API\App;

// Load and start application
$app = App::getInstance();
$app->init()->execute();

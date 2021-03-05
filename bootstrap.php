<?php
/**
 * OpenCCK - CMS Backend bootstrap
 *
 * @package openCCK
 * @author Krupkin Sergey <rekryt@yandex.ru>
 */
defined('PATH_ROOT') or define('PATH_ROOT', __DIR__);
define('PATH_INCLUDES', __DIR__ . '/vendor/opencck/api');

// load dependencies
include 'vendor/autoload.php';
// load functions
include PATH_INCLUDES . '/lib/functions.php';
// load definitions
include PATH_INCLUDES . '/lib/definitions.php';

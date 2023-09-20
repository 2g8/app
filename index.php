<?php
defined('APP_PATH') or define('APP_PATH', dirname(__FILE__));

include_once "config.php";

define('STATIC_PATH', '/assets/');
require_once(APP_PATH.DIRECTORY_SEPARATOR."vendor/teaphp/framework/src/tea.php");